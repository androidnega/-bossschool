<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->string('ges_region', 64)->nullable()->after('address');
            $table->string('ges_district', 64)->nullable()->after('ges_region');
            $table->string('ges_circuit', 64)->nullable()->after('ges_district');
            $table->string('school_code', 32)->nullable()->after('ges_circuit');
            $table->string('head_teacher_name', 128)->nullable()->after('school_code');
            $table->string('motto', 191)->nullable()->after('head_teacher_name');
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->string('admission_no', 64)->nullable()->after('name');
            $table->index(['tenant_id', 'admission_no']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'admission_no']);
            $table->dropColumn('admission_no');
        });

        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn(['ges_region', 'ges_district', 'ges_circuit', 'school_code', 'head_teacher_name', 'motto']);
        });
    }
};
