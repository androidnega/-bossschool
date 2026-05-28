<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-(student, year, term) bag of "soft" fields that show on the Ghanaian
 * report card: conduct, attitude, interest, class teacher / head teacher
 * remarks, attendance summary, position/rank, next term fee, vacation and
 * reopening dates, and signature placeholders.
 *
 * Keeping this off the `results` table because results are per subject and
 * a student has many subjects; the report card needs ONE row per student
 * per term.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_meta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();

            $table->unsignedInteger('days_school_opened')->nullable();
            $table->unsignedInteger('days_present')->nullable();
            $table->unsignedInteger('days_absent')->nullable();
            $table->unsignedInteger('position_in_class')->nullable();
            $table->unsignedInteger('class_size')->nullable();

            $table->string('conduct', 64)->nullable();
            $table->string('attitude', 64)->nullable();
            $table->string('interest', 64)->nullable();

            $table->text('class_teacher_remark')->nullable();
            $table->text('head_teacher_remark')->nullable();

            $table->decimal('next_term_fee', 12, 2)->nullable();
            $table->date('vacation_date')->nullable();
            $table->date('reopening_date')->nullable();

            $table->string('class_teacher_signature', 191)->nullable();
            $table->string('head_teacher_signature', 191)->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'academic_year_id', 'term_id'], 'report_card_meta_uniq');
            $table->index(['tenant_id', 'academic_year_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_meta');
    }
};
