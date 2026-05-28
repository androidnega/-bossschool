<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenants', 'sms_credits_balance')) {
                // Balance is stored as integer count of SMS messages remaining.
                // We bill in whole SMS units even though the price (in GHS)
                // is a small decimal; this keeps the counter exact.
                $table->bigInteger('sms_credits_balance')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            if (Schema::hasColumn('tenants', 'sms_credits_balance')) {
                $table->dropColumn('sms_credits_balance');
            }
        });
    }
};
