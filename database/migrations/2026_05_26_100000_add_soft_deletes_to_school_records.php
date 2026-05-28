<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft-delete columns to school records that must remain auditable
     * even after they've been "removed" by school staff.
     */
    public function up(): void
    {
        foreach (['students', 'fees', 'payments', 'results', 'staff'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t): void {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['students', 'fees', 'payments', 'results', 'staff'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t): void {
                $t->dropSoftDeletes();
            });
        }
    }
};
