<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'onboarding_complete')) {
                $table->boolean('onboarding_complete')->default(false)->after('status');
            }
            if (! Schema::hasColumn('tenants', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_complete');
            }
        });

        if (! Schema::hasTable('tenant_onboarding_progress')) {
            Schema::create('tenant_onboarding_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->unsignedTinyInteger('current_step')->default(1);
                $table->json('completed_steps')->nullable();
                $table->json('payload')->nullable();
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->unique('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_progress');
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['onboarding_complete', 'onboarding_completed_at']);
        });
    }
};
