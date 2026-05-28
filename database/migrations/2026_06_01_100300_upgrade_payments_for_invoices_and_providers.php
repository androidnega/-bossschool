<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extend the Phase 1 payments table for Phase 3:
 *  - link to fee_invoices (optional — manual entries without an invoice
 *    still work, but legacy demo data continues to render)
 *  - track which staff member received the cash / posted the entry
 *  - record gateway provider info for future Mobile Money integrations
 *  - introduce an explicit status (pending / successful / failed / reversed)
 *    so we never hard-delete a financial record again
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('fee_invoice_id')
                ->nullable()
                ->after('student_id')
                ->constrained('fee_invoices')
                ->nullOnDelete();
            $table->foreignId('received_by_user_id')
                ->nullable()
                ->after('fee_invoice_id')
                ->constrained('users')
                ->nullOnDelete();

            // method was an enum(momo,cash,bank) — drop the column and replace
            // with a string so we can add new channels safely without another
            // schema change. Existing data is migrated below.
        });

        // Sqlite (used by tests) does not let us ALTER an existing enum column
        // in place, so we use a temporary column dance to keep the data.
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('payment_channel', 16)->nullable()->after('amount');
            // cash | momo | bank | card | cheque | gateway
            $table->string('payment_reference', 191)->nullable()->after('reference');
            $table->string('provider', 32)->nullable()->after('payment_channel');
            // manual | hubtel | paystack | flutterwave | expresspay
            $table->string('provider_reference', 191)->nullable()->after('provider');
            $table->string('status', 16)->default('successful')->after('provider_reference');
            // pending | successful | failed | reversed
            $table->foreignId('reversed_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable()->after('reversed_by_user_id');
            $table->text('reversal_reason')->nullable()->after('reversed_at');
        });

        // Copy data: method -> payment_channel, reference -> payment_reference.
        DB::table('payments')->whereNotNull('method')->update([
            'payment_channel' => DB::raw('method'),
            'provider' => 'manual',
            'status' => 'successful',
        ]);
        DB::statement('UPDATE payments SET payment_reference = reference WHERE reference IS NOT NULL');

        // Drop the legacy enum column on databases that allow it.
        if (Schema::hasColumn('payments', 'method')) {
            try {
                Schema::table('payments', function (Blueprint $table): void {
                    $table->dropColumn('method');
                });
            } catch (\Throwable) {
                // sqlite < 3.35 doesn't support DROP COLUMN — leave the column
                // in place; the model no longer reads it.
            }
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['tenant_id', 'fee_invoice_id']);
            $table->index(['tenant_id', 'payment_channel']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'provider']);
            // Receipt id was already indexed in Phase 1.
        });
    }

    public function down(): void
    {
        // Best-effort rollback — re-create the enum column and copy values back.
        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'method')) {
                $table->string('method', 16)->nullable();
            }
        });

        if (Schema::hasColumn('payments', 'payment_channel')) {
            DB::statement('UPDATE payments SET method = payment_channel WHERE payment_channel IS NOT NULL');
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropForeign(['fee_invoice_id']);
            $table->dropForeign(['received_by_user_id']);
            $table->dropForeign(['reversed_by_user_id']);
            $table->dropColumn([
                'fee_invoice_id',
                'received_by_user_id',
                'payment_channel',
                'payment_reference',
                'provider',
                'provider_reference',
                'status',
                'reversed_by_user_id',
                'reversed_at',
                'reversal_reason',
            ]);
        });
    }
};
