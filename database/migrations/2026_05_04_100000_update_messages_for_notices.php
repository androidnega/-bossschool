<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unsignedBigInteger('recipient_id')->nullable()->change();
            $table->string('title')->nullable()->after('tenant_id');
            $table->string('audience', 512)->nullable()->after('title');
            $table->string('status', 32)->default('sent')->after('channel');
            $table->foreignId('school_class_id')->nullable()->after('recipient_id')->constrained('classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
            $table->dropColumn(['school_class_id', 'status', 'audience', 'title']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('recipient_id')->nullable(false)->change();
        });
    }
};
