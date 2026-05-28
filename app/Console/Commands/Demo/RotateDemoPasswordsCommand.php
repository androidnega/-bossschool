<?php

namespace App\Console\Commands\Demo;

use App\Services\ActivityLogger;
use App\Services\ProductionChecklistService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('demo:rotate-passwords {--dry-run : List affected accounts without changing anything} {--force : Skip confirmation prompt} {--out= : Optional file path on the local disk to write the new credentials to}')]
#[Description('Generate fresh random passwords for any demo accounts that still use the default credential. New passwords are written to a private file, never printed to stdout.')]
class RotateDemoPasswordsCommand extends Command
{
    public function handle(ProductionChecklistService $service, ActivityLogger $logger): int
    {
        $candidates = $service->demoUserQuery()->get();
        $targets = $candidates->filter(fn ($u) => Hash::check('password', (string) $u->password));

        if ($targets->isEmpty()) {
            $this->info('No demo accounts need rotating.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->line(sprintf('%s %d accounts:', $dryRun ? 'Would rotate' : 'Rotating', $targets->count()));
        foreach ($targets as $u) {
            $this->line('  - #'.$u->id.'  '.$u->email);
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        if (! $this->option('force') && $this->input->isInteractive()) {
            if (! $this->confirm('Generate new passwords and overwrite existing ones?', false)) {
                return self::SUCCESS;
            }
        }

        $output = [];
        foreach ($targets as $u) {
            $new = Str::random(20);
            $u->forceFill(['password' => bcrypt($new), 'remember_token' => null])->save();
            $output[] = sprintf('%s\t%s', $u->email, $new);

            $logger->log(
                'demo_password_rotated',
                sprintf('Demo password rotated for %s.', $u->email),
                ['user_id' => $u->id, 'email' => $u->email],
                $u->tenant_id ? (int) $u->tenant_id : null,
                \App\Models\User::class,
                (int) $u->id
            );
        }

        $path = (string) ($this->option('out') ?: 'demo-credentials-'.now()->format('Ymd-His').'.tsv');
        Storage::disk('local')->put($path, implode("\n", $output)."\n");

        $this->info(sprintf('New credentials written to local disk at: %s (chmod 600 recommended).', $path));
        $this->warn('Passwords are NOT printed to stdout. Read the file privately.');

        return self::SUCCESS;
    }
}
