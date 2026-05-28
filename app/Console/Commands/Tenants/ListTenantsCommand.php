<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:list')]
#[Description('List all tenants with id, name, subdomain, plan, status, student count, staff count.')]
class ListTenantsCommand extends Command
{
    public function handle(): int
    {
        $tenants = Tenant::query()
            ->with(['plan'])
            ->withCount(['students', 'staff'])
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Subdomain', 'Plan', 'Status', 'Students', 'Staff'],
            $tenants->map(fn ($t) => [
                $t->id,
                $t->name,
                $t->subdomain,
                $t->plan?->name ?? '—',
                $t->status,
                $t->students_count,
                $t->staff_count,
            ])->all()
        );

        return self::SUCCESS;
    }
}
