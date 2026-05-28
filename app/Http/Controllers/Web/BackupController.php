<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backup\CreateBackupRequest;
use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\Backup\TenantBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(private readonly TenantBackupService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', TenantBackup::class);

        $tenantId = (int) auth()->user()->tenant_id;
        $backups = TenantBackup::query()
            ->where('tenant_id', $tenantId)
            ->with(['creator', 'restorer'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('backups.index', ['backups' => $backups]);
    }

    public function store(CreateBackupRequest $request): RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail((int) $request->user()->tenant_id);
        $backup = $this->service->create($tenant, (string) $request->input('backup_type'), $request->user());

        if ($backup->status === TenantBackup::STATUS_COMPLETED) {
            return redirect()->route('backups.index')->with('status', __('Backup created.'));
        }

        return redirect()->route('backups.index')->withErrors(['backup_type' => $backup->failure_reason ?: __('Backup failed.')]);
    }

    public function download(TenantBackup $backup): StreamedResponse
    {
        $this->authorize('download', $backup);

        $disk = Storage::disk((string) $backup->file_disk);
        if (! $disk->exists($backup->file_path)) {
            abort(404);
        }

        return $disk->download($backup->file_path, sprintf('school-backup-%s-%d.json',
            $backup->backup_type,
            $backup->id
        ));
    }
}
