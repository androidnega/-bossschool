<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\GhanaBasicSchoolTemplateSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\PlansSeeder;
use Database\Seeders\PlatformBootstrapSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production cutover command.
 *
 * Purges demo / sample data and re-seeds ONLY production-safe catalogue data
 * (plans, permissions, platform settings, feature toggles, maintenance mode,
 * Ghana school templates).
 *
 *   php artisan production:prepare              # safety-blocks unless APP_ENV=production
 *   php artisan production:prepare --i-mean-it  # force-run even outside production
 *   php artisan production:prepare --dry-run    # show what would happen, change nothing
 *
 * KEPT (never touched):
 *   plans, permissions, role_permissions, platform_settings,
 *   feature_toggles, maintenance_modes, migrations, the real SuperAdmin
 *   (tenant_id IS NULL, role = SuperAdmin), and the new school_template_*
 *   catalogue tables.
 *
 * CLEARED:
 *   tenants (and everything cascading off them — schools, academic_years,
 *   terms, classes, subjects, students, staff, fees, payments, results,
 *   attendance, messages, pivot tables, …), all users with tenant_id set,
 *   activity_logs, sessions, cache, jobs, password_reset_tokens, and
 *   personal_access_tokens.
 */
#[Signature('production:prepare {--i-mean-it : Run even if APP_ENV is not production} {--dry-run : Show what would happen without changing anything} {--force : Skip the interactive confirmation}')]
#[Description('Purge demo data and re-seed production-safe catalogue + Ghana school templates.')]
class ProductionPrepareCommand extends Command
{
    public function handle(): int
    {
        $env = (string) config('app.env');
        $iMeanIt = (bool) $this->option('i-mean-it');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if ($env !== 'production' && ! $iMeanIt) {
            $this->error("Refusing to run: APP_ENV is '{$env}', not 'production'. Pass --i-mean-it to override.");

            return self::FAILURE;
        }

        if (! $force && $this->input->isInteractive()) {
            $this->warn('This will PERMANENTLY delete every tenant and every per-tenant user.');
            $this->line(' Kept: plans, permissions, platform_settings, feature_toggles, maintenance_modes, migrations, school templates, and the real SuperAdmin.');
            if (! $this->confirm('Are you absolutely sure you want to proceed?', false)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $report = [];

        if ($dryRun) {
            $this->warn('--dry-run enabled — no changes will be persisted.');
        }

        // Order matters: users.tenant_id uses nullOnDelete (not cascade),
        // so we must purge tenant-scoped users BEFORE the tenants, otherwise
        // the users get orphaned with tenant_id=NULL and survive the purge.
        $this->info('1/6 · Purging tenant data…');
        $report['users_deleted'] = $this->purgeTenantUsers($dryRun);
        $report['tenants_deleted'] = $this->purgeTenants($dryRun);

        $this->info('2/6 · Clearing sessions, cache, jobs and tokens…');
        $report['session_rows_deleted'] = $this->purgeSessions($dryRun);
        $report['cache_rows_deleted'] = $this->purgeCache($dryRun);
        $report['job_rows_deleted'] = $this->purgeJobs($dryRun);
        $report['token_rows_deleted'] = $this->purgeTokens($dryRun);

        if (! $dryRun) {
            $this->info('3/6 · Seeding plans + permissions + platform bootstrap…');
            $this->call('db:seed', ['--class' => PlansSeeder::class, '--force' => true]);
            $this->call('db:seed', ['--class' => PermissionsSeeder::class, '--force' => true]);
            $this->call('db:seed', ['--class' => PlatformBootstrapSeeder::class, '--force' => true]);

            $this->info('4/6 · Seeding Ghana school templates…');
            $this->call('db:seed', ['--class' => GhanaBasicSchoolTemplateSeeder::class, '--force' => true]);

            $this->info('5/6 · Ensuring a SuperAdmin exists (without changing an existing one)…');
            $report['super_admin'] = $this->ensureSuperAdmin();

            // Clear seeded activity logs LAST — PlatformBootstrapSeeder
            // (re-run above) inserts ~16 sample audit rows that don't
            // belong in production.
            $this->info('6/6 · Clearing seeded activity logs…');
            $report['activity_logs_deleted'] = $this->purgeActivityLogs(false);
        } else {
            $this->line('  (seeding skipped under --dry-run)');
            $report['activity_logs_deleted'] = $this->purgeActivityLogs(true);
        }

        $this->newLine();
        $this->table(['Step', 'Result'], collect($report)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : (string) $v])->values()->all());
        $this->info($dryRun ? 'Dry run complete — no data was changed.' : 'Production prepare complete.');

        return self::SUCCESS;
    }

    /* ─────────────────────────── purges ────────────────────────────── */

    private function purgeTenants(bool $dryRun): int
    {
        // Foreign keys with cascadeOnDelete on tenant_id mean deleting a
        // tenant row cascades through every per-tenant table. Use SoftDeletes
        // -aware forceDelete so the row is physically removed.
        $ids = Tenant::query()->withTrashed()->pluck('id');
        if ($dryRun || $ids->isEmpty()) {
            return $ids->count();
        }

        return DB::transaction(function () use ($ids): int {
            $deleted = 0;
            foreach ($ids as $id) {
                /** @var Tenant|null $t */
                $t = Tenant::withTrashed()->find($id);
                if ($t) {
                    $t->forceDelete();
                    $deleted++;
                }
            }

            return $deleted;
        });
    }

    private function purgeTenantUsers(bool $dryRun): int
    {
        // Every user attached to a tenant (Admins, Teachers, Parents …).
        // SuperAdmins (tenant_id NULL) are NEVER touched.
        $count = User::query()
            ->withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->count();
        if ($dryRun || $count === 0) {
            return $count;
        }

        return (int) User::query()
            ->withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->delete();
    }

    private function purgeActivityLogs(bool $dryRun): int
    {
        if (! Schema::hasTable('activity_logs')) {
            return 0;
        }
        $count = ActivityLog::query()->count();
        if ($dryRun || $count === 0) {
            return $count;
        }

        return (int) ActivityLog::query()->delete();
    }

    private function purgeSessions(bool $dryRun): int
    {
        return $this->purgeTable('sessions', $dryRun);
    }

    private function purgeCache(bool $dryRun): int
    {
        return $this->purgeTable('cache', $dryRun) + $this->purgeTable('cache_locks', $dryRun);
    }

    private function purgeJobs(bool $dryRun): int
    {
        return $this->purgeTable('jobs', $dryRun)
            + $this->purgeTable('failed_jobs', $dryRun)
            + $this->purgeTable('job_batches', $dryRun);
    }

    private function purgeTokens(bool $dryRun): int
    {
        return $this->purgeTable('password_reset_tokens', $dryRun)
            + $this->purgeTable('personal_access_tokens', $dryRun);
    }

    private function purgeTable(string $name, bool $dryRun): int
    {
        if (! Schema::hasTable($name)) {
            return 0;
        }
        $count = (int) DB::table($name)->count();
        if ($dryRun || $count === 0) {
            return $count;
        }

        return (int) DB::table($name)->delete();
    }

    /* ───────────────────────── superadmin ──────────────────────────── */

    /**
     * Ensure exactly one SuperAdmin exists. If one already exists, leave it
     * alone (don't touch its password). If none, create one with a random
     * 32-character password and surface the value in the report.
     */
    private function ensureSuperAdmin(): array
    {
        $existing = User::query()
            ->withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('role', UserRole::SuperAdmin->value)
            ->first();

        if ($existing) {
            return ['action' => 'kept', 'email' => $existing->email];
        }

        $password = bin2hex(random_bytes(16));
        $user = User::query()->create([
            'tenant_id' => null,
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@bossschool.com',
            'password' => $password,
            'role' => UserRole::SuperAdmin->value,
            'force_password_reset' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return ['action' => 'created', 'email' => $user->email, 'password' => $password];
    }
}
