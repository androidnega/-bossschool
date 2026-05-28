<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('billing_cycle', 16)->default('monthly')->after('price');
            $table->boolean('is_active')->default(true)->after('features');
            $table->unsignedSmallInteger('sort_order')->nullable()->after('is_active');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->nullable()->after('plan_id');
            $table->string('billing_cycle', 16)->nullable()->after('amount');
            $table->text('note')->nullable()->after('status');
            $table->foreignId('changed_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 32)->nullable();
            $table->string('group', 64)->nullable()->index();
            $table->timestamps();
        });

        Schema::create('feature_toggles', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('scope', 16)->default('global')->index();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'tenant_id']);
        });

        Schema::create('maintenance_modes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->text('message')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('enabled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_enabled']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_role')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('action', 64)->index();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('maintenance_modes');
        Schema::dropIfExists('feature_toggles');
        Schema::dropIfExists('platform_settings');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['changed_by']);
            $table->dropColumn(['amount', 'billing_cycle', 'note', 'changed_by']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'is_active', 'sort_order']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
