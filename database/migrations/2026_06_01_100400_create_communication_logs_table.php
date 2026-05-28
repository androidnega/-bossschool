<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SMS-ready communication log. We don't send anything yet — the row
 * is created with status=queued or status=skipped (when no phone is on
 * file) so a future provider integration can pick it up.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_phone', 32)->nullable();
            $table->string('recipient_email', 191)->nullable();

            $table->string('channel', 16);   // sms | email | whatsapp | in_app
            $table->string('purpose', 32);   // fee_reminder | attendance_alert | result_notice | announcement | general
            $table->string('subject', 191)->nullable();
            $table->text('message');

            $table->string('status', 16)->default('queued');
            // queued | sent | failed | skipped

            $table->string('provider', 32)->nullable();
            $table->string('provider_reference', 191)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'channel']);
            $table->index(['tenant_id', 'purpose']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'recipient_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
