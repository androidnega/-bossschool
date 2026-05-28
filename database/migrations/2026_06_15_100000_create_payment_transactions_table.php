<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online payment attempts initiated through a gateway (Hubtel, Paystack,
 * Flutterwave, ExpressPay). Each row is an idempotent record for one
 * checkout. Webhooks update the row by `provider` + `provider_reference`,
 * never creating duplicates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('fee_invoice_id')->nullable()->constrained('fee_invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('provider', 32);                       // hubtel|paystack|flutterwave|expresspay
            $table->string('provider_reference', 191)->nullable();
            $table->string('checkout_url', 500)->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 8)->default('GHS');
            $table->string('status', 16)->default('initiated');   // initiated|pending|successful|failed|cancelled

            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('raw_webhook')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'fee_invoice_id']);
            $table->index(['tenant_id', 'status']);
            $table->unique(['provider', 'provider_reference']); // global idempotency key
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
