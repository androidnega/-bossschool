<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_incidents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('incident_date');
            $table->string('category', 64); // e.g. fighting, lateness, vandalism
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->string('severity', 16)->default('low');   // low|medium|high|critical
            $table->string('status', 16)->default('open');    // open|resolved|escalated
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'severity']);
            $table->index(['tenant_id', 'incident_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_incidents');
    }
};
