<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_type', 255);
            $table->unsignedBigInteger('recipient_id');
            $table->string('channel', 64)->nullable();
            $table->text('content');
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();

            $table->index(['tenant_id', 'sent_at']);
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
