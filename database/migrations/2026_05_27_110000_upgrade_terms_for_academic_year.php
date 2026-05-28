<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrades the existing simple "terms" table to proper Ghanaian academic
 * terms (Term 1/2/3 per academic year, with dates and a single current
 * term per tenant).
 *
 * Old rows that don't yet have an academic year are left with
 * academic_year_id NULL. A best-effort backfill is done below if the
 * tenant already has a current academic year; otherwise tenants will see
 * a friendly nudge to set the academic year per term in the UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terms', function (Blueprint $table): void {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('academic_years')
                ->nullOnDelete();
            $table->unsignedSmallInteger('term_order')->default(0)->after('name');
            $table->date('starts_on')->nullable()->after('term_order');
            $table->date('ends_on')->nullable()->after('starts_on');
            $table->boolean('is_current')->default(false)->after('ends_on');
            $table->string('status', 16)->default('active')->after('is_current');

            $table->index(['tenant_id', 'academic_year_id']);
            $table->index(['tenant_id', 'is_current']);
        });

        // Best-effort backfill: order existing terms alphabetically and
        // number them 1..N within each tenant so old data keeps working.
        $terms = DB::table('terms')->orderBy('tenant_id')->orderBy('name')->orderBy('id')->get();
        $orderByTenant = [];
        foreach ($terms as $term) {
            $orderByTenant[$term->tenant_id] = ($orderByTenant[$term->tenant_id] ?? 0) + 1;
            DB::table('terms')->where('id', $term->id)->update([
                'term_order' => $orderByTenant[$term->tenant_id],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table): void {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['tenant_id', 'academic_year_id']);
            $table->dropIndex(['tenant_id', 'is_current']);
            $table->dropColumn([
                'academic_year_id',
                'term_order',
                'starts_on',
                'ends_on',
                'is_current',
                'status',
            ]);
        });
    }
};
