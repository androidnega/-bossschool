<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('academic_years')
                ->nullOnDelete();
            $table->foreignId('term_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('terms')
                ->nullOnDelete();
        });

        // Best-effort backfill: for any tenant that already has a current
        // academic year + current term, attach existing results to it so we
        // don't lose old data.
        $tenants = DB::table('results')->distinct()->pluck('tenant_id');
        foreach ($tenants as $tenantId) {
            $year = DB::table('academic_years')
                ->where('tenant_id', $tenantId)
                ->where('is_current', true)
                ->orderByDesc('id')
                ->value('id');
            $term = DB::table('terms')
                ->where('tenant_id', $tenantId)
                ->where('is_current', true)
                ->orderByDesc('id')
                ->value('id');

            if ($year !== null) {
                DB::table('results')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('academic_year_id')
                    ->update(['academic_year_id' => $year]);
            }
            if ($term !== null) {
                DB::table('results')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('term_id')
                    ->update(['term_id' => $term]);
            }
        }

        // Swap unique key: (tenant, student, subject) -> include year + term.
        Schema::table('results', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'student_id', 'subject_id']);
            $table->unique(
                ['tenant_id', 'student_id', 'subject_id', 'academic_year_id', 'term_id'],
                'results_uniq_student_subject_term'
            );
            $table->index(['tenant_id', 'academic_year_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $table->dropUnique('results_uniq_student_subject_term');
            $table->unique(['tenant_id', 'student_id', 'subject_id']);
            $table->dropIndex(['tenant_id', 'academic_year_id', 'term_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);
            $table->dropColumn(['academic_year_id', 'term_id']);
        });
    }
};
