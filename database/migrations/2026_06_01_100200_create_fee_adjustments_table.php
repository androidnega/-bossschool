<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Discounts, scholarships, and waivers. Created by Accountant (or
 * Admin/Proprietor), approved by Admin/Proprietor. Only approved
 * adjustments reduce invoice balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('fee_invoice_id')->nullable()->constrained('fee_invoices')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();

            $table->string('type', 16); // discount | scholarship | waiver
            $table->string('description', 191)->nullable();
            $table->decimal('amount', 14, 2)->default(0);

            $table->string('status', 16)->default('pending');
            // pending | approved | rejected | cancelled

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'fee_invoice_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_adjustments');
    }
};
