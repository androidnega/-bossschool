<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table): void {
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
            $table->foreignId('marked_by_user_id')
                ->nullable()
                ->after('remarks')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['tenant_id', 'academic_year_id', 'term_id']);
            $table->index(['tenant_id', 'term_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table): void {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);
            $table->dropForeign(['marked_by_user_id']);
            $table->dropIndex(['tenant_id', 'academic_year_id', 'term_id']);
            $table->dropIndex(['tenant_id', 'term_id', 'date']);
            $table->dropColumn(['academic_year_id', 'term_id', 'marked_by_user_id']);
        });
    }
};
