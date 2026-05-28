<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make payment_transactions usable for non-invoice purchases (SMS credits,
 * subscriptions) without forcing a student_id. We also add a `purpose`
 * discriminator and a `metadata` JSON blob so the webhook handler can apply
 * the right downstream effect.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_transactions')) {
            return;
        }

        Schema::table('payment_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('payment_transactions', 'purpose')) {
                // fee_invoice (default, legacy) | sms_credits | subscription
                $table->string('purpose', 32)->default('fee_invoice')->after('initiated_by_user_id');
            }
            if (! Schema::hasColumn('payment_transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('raw_webhook');
            }
        });

        // student_id used to be NOT NULL because every payment was a school-fee
        // payment. SMS-credit and subscription purchases have no student, so
        // relax it. We drop the FK first, alter the column to nullable, then
        // re-add the FK with the (existing) NULL-on-delete behaviour.
        if (Schema::hasColumn('payment_transactions', 'student_id')) {
            try {
                Schema::table('payment_transactions', function (Blueprint $table): void {
                    $table->dropForeign(['student_id']);
                });
            } catch (\Throwable $e) {
                // FK may not exist by this name on every DB driver — ignore.
            }

            Schema::table('payment_transactions', function (Blueprint $table): void {
                $table->foreignId('student_id')->nullable()->change();
            });

            try {
                Schema::table('payment_transactions', function (Blueprint $table): void {
                    $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Already restored on some drivers — ignore.
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_transactions')) {
            return;
        }

        Schema::table('payment_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('payment_transactions', 'purpose')) {
                $table->dropColumn('purpose');
            }
            if (Schema::hasColumn('payment_transactions', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
