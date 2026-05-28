<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_credit_transactions')) {
            return;
        }

        Schema::create('sms_credit_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // +ve for grants (Paystack purchase, manual SuperAdmin top-up),
            // -ve for debits (an SMS we actually sent), +ve again for refunds
            // (provider rejected the send → we re-credit).
            $table->bigInteger('delta');

            // Running balance AFTER this row was applied — recorded for audit
            // so we can verify the ledger by re-summing deltas later.
            $table->bigInteger('balance_after');

            // Semantic reason. Constrained at the application layer; left
            // free-form in the column for future expansion.
            //   purchase | manual_grant | sms_debit | sms_refund | adjustment
            $table->string('reason', 32);

            // Audit links (all nullable):
            //   payment_transaction_id → the Paystack purchase that funded this grant
            //   communication_log_id   → the SMS row that triggered this debit/refund
            //   actor_id              → the user who initiated a manual grant
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->foreignId('communication_log_id')->nullable()->constrained('communication_logs')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
    }
};
