<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound('currentTenant')) {
            return;
        }

        /** @var Tenant $tenant */
        $tenant = app('currentTenant');

        $builder->where($model->getTable().'.tenant_id', $tenant->id);
    }
}
