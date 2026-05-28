<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual line items on a fee invoice. The optional `fee_id` lets us
 * trace back to the class-level Fee template the row was generated from
 * (when present) while keeping per-student edits stable even if the
 * template later changes or is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_id')->nullable()->constrained('fees')->nullOnDelete();

            $table->string('description', 191);
            $table->string('category', 64)->nullable();
            // categories help reports + statements (e.g. tuition, feeding, transport,
            // uniform, books, exam, arrears_adjustment, pta)

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->timestamps();

            $table->index(['tenant_id', 'fee_invoice_id']);
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_items');
    }
};
