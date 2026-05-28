<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant model uses SoftDeletes; older databases may lack deleted_at if
     * platform_control migrations were never applied.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        if (! Schema::hasColumn('tenants', 'deleted_at')) {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'deleted_at')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
