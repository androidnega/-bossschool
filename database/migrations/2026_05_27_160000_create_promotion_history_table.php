<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only ledger of every promotion / repeat / graduation action so
 * old class history and old results stay intact when a student moves to
 * the next class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('to_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('from_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('to_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('promoted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16); // promoted | repeated | graduated
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'to_academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_history');
    }
};
