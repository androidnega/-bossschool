<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backup\CreateBackupRequest;
use App\Http\Requests\Backup\RestoreBackupRequest;
use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\Backup\TenantBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantBackupController extends Controller
{
    public function __construct(private readonly TenantBackupService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TenantBackup::class);

        $backups = TenantBackup::query()
            ->with(['tenant', 'creator', 'restorer'])
            ->when($request->filled('tenant_id'), fn ($q) => $q->where('tenant_id', (int) $request->input('tenant_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('backup_type', $request->string('type')))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('platform.backups.index', [
            'backups' => $backups,
            'tenants' => Tenant::query()->orderBy('name')->get(),
            'filters' => [
                'tenant_id' => $request->input('tenant_id'),
                'type' => $request->input('type'),
            ],
        ]);
    }

    public function store(CreateBackupRequest $request): RedirectResponse
    {
        $tenantId = (int) $request->input('tenant_id');
        $tenant = Tenant::query()->findOrFail($tenantId);

        $backup = $this->service->create($tenant, (string) $request->input('backup_type'), $request->user());

        if ($backup->status === TenantBackup::STATUS_COMPLETED) {
            return redirect()->route('platform.backups.index')->with('status', __('Backup created.'));
        }

        return redirect()->route('platform.backups.index')->withErrors([
            'backup_type' => $backup->failure_reason ?: __('Backup failed.'),
        ]);
    }

    public function show(TenantBackup $backup): View
    {
        $this->authorize('view', $backup);
        $backup->load(['tenant', 'creator', 'restorer']);
        $checksumValid = $backup->status === TenantBackup::STATUS_COMPLETED || $backup->status === TenantBackup::STATUS_RESTORED
            ? $this->service->verifyChecksum($backup)
            : null;

        return view('platform.backups.show', compact('backup', 'checksumValid'));
    }

    public function download(TenantBackup $backup): StreamedResponse
    {
        $this->authorize('download', $backup);

        $disk = Storage::disk((string) $backup->file_disk);
        if (! $disk->exists($backup->file_path)) {
            abort(404);
        }

        return $disk->download($backup->file_path, sprintf('tenant-%d-%s-%d.json',
            $backup->tenant_id,
            $backup->backup_type,
            $backup->id
        ));
    }

    public function restore(RestoreBackupRequest $request, TenantBackup $backup): RedirectResponse
    {
        $this->authorize('restore', $backup);

        $user = $request->user();
        if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('Password incorrect.'),
            ]);
        }

        $target = $request->filled('target_tenant_id') ? (int) $request->input('target_tenant_id') : null;

        try {
            $this->service->restore($backup, $user, $target);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['confirm' => $e->getMessage()]);
        }

        return redirect()->route('platform.backups.show', $backup)->with('status', __('Backup restored.'));
    }
}
