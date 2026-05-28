<?php

namespace Database\Seeders;

use App\Http\Requests\Platform\UpdateFeatureTogglesRequest;
use App\Models\ActivityLog;
use App\Models\FeatureToggle;
use App\Models\MaintenanceMode;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'platform_name' => ['BossSchool', 'string', 'general'],
            'support_email' => ['support@bossschool.com', 'string', 'general'],
            'support_phone' => ['+233 00 000 0000', 'string', 'general'],
            'default_trial_days' => ['14', 'int', 'billing'],
            'allow_school_registration' => ['1', 'bool', 'access'],
            'require_subscription_payment' => ['0', 'bool', 'billing'],
            'default_currency' => ['GHS', 'string', 'billing'],
            'maintenance_enabled' => ['0', 'bool', 'maintenance'],
            'maintenance_message' => ['We are upgrading BossSchool. Please try again soon.', 'string', 'maintenance'],
        ];

        foreach ($settings as $key => [$value, $type, $group]) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type, 'group' => $group]
            );
        }
        PlatformSetting::forgetCache();

        $toggleDefaults = [
            'parent_portal' => true,
            'student_portal' => true,
            'online_payments' => false,
            'attendance' => true,
            'messaging' => true,
            'report_cards' => true,
            'tenant_registration' => true,
            'maintenance_banner' => true,
        ];

        foreach (UpdateFeatureTogglesRequest::GLOBAL_KEYS as $key) {
            FeatureToggle::query()->updateOrCreate(
                ['key' => $key, 'tenant_id' => null],
                [
                    'name' => ucfirst(str_replace('_', ' ', $key)),
                    'description' => null,
                    'is_enabled' => $toggleDefaults[$key] ?? true,
                    'scope' => FeatureToggle::SCOPE_GLOBAL,
                ]
            );
        }

        MaintenanceMode::query()->updateOrCreate(
            ['tenant_id' => null],
            [
                'is_enabled' => false,
                'message' => null,
                'starts_at' => null,
                'ends_at' => null,
                'enabled_by' => null,
            ]
        );

        $super = User::withoutGlobalScopes()->where('email', 'superadmin@bossschool.com')->first();
        $demo = Tenant::query()->where('subdomain', 'demo')->first();
        $bright = Tenant::query()->where('subdomain', 'bright-future')->first();
        $plan = Plan::query()->where('name', 'Growth')->first();

        $actorId = $super?->id;
        $actorName = $super?->name;
        $actorRole = $super?->role;

        $logs = [
            ['tenant_created', 'Tenant created: Evergreen Academy (Demo School)', ['subdomain' => 'demo'], $demo?->id],
            ['tenant_created', 'Tenant created: Bright Future Academy', ['subdomain' => 'bright-future'], $bright?->id],
            ['plan_updated', 'Updated plan Growth', ['plan_id' => $plan?->id], null],
            ['subscription_changed', 'Subscription changed for demo tenant', ['status' => 'active'], $demo?->id],
            ['feature_toggle_changed', 'Global feature toggles updated', ['keys' => ['online_payments']], null],
            ['maintenance_disabled', 'Global maintenance disabled', [], null],
            ['user_created', 'Tenant user created: admin@demo.com', ['role' => 'Admin'], $demo?->id],
            ['login', 'User logged in', ['email' => 'superadmin@bossschool.com'], null],
            ['logout', 'User logged out', [], null],
            ['settings_updated', 'Platform settings updated', ['keys' => ['platform_name']], null],
            ['tenant_suspended', 'Tenant suspended: Grace Valley School', [], Tenant::query()->where('subdomain', 'grace-valley')->value('id')],
            ['tenant_activated', 'Tenant activated: Grace Valley School', [], Tenant::query()->where('subdomain', 'grace-valley')->value('id')],
            ['plan_created', 'Created plan Enterprise', ['plan_id' => null], null],
            ['subscription_changed', 'Subscription extended', ['days' => 30], $demo?->id],
            ['activity_log', 'Sample platform audit entry', ['source' => 'seed'], $demo?->id],
            ['user_updated', 'Tenant user updated: admin@demo.com', [], $demo?->id],
        ];

        foreach ($logs as $i => $row) {
            [$action, $description, $metadata, $tenantId] = $row;
            $at = now()->subMinutes(5 * ($i + 1));
            ActivityLog::query()->create([
                'actor_id' => $actorId,
                'actor_name' => $actorName,
                'actor_role' => $actorRole,
                'tenant_id' => $tenantId,
                'target_type' => null,
                'target_id' => null,
                'action' => $action,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PlatformBootstrapSeeder',
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }
}
