<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Message;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Payments\PaymentProviderRegistry::class, function ($app): \App\Services\Payments\PaymentProviderRegistry {
            return new \App\Services\Payments\PaymentProviderRegistry(
                (array) config('payments.providers', [])
            );
        });

        $this->app->singleton(\App\Services\Sms\SmsProviderRegistry::class, function ($app): \App\Services\Sms\SmsProviderRegistry {
            return new \App\Services\Sms\SmsProviderRegistry(
                (array) config('sms.providers', [])
            );
        });

        $this->app->singleton(\App\Services\TenantSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRateLimiters();

        Gate::define('platform.manage', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.view', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.manageTenants', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.manageTenantUsers', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.viewTenantStudents', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.viewTenantFinance', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.manageTenantSubscription', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('platform.resetTenantData', fn (User $user): bool => $user->isSuperAdmin());

        Gate::define('reports.finance', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Proprietor->value,
                UserRole::Accountant->value,
            ], true);
        });

        Gate::define('reports.students', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
                UserRole::Teacher->value,
            ], true);
        });

        Gate::define('reports.academic', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
                UserRole::Teacher->value,
            ], true);
        });

        Gate::define('reports.overview', function (User $user): bool {
            return Gate::forUser($user)->check('reports.finance')
                || Gate::forUser($user)->check('reports.students')
                || Gate::forUser($user)->check('reports.academic');
        });

        Gate::define('billing.view', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('billing.manage', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('settings.manage', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('staff.view', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('staff.manage', function (User $user): bool {
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('manageAssignments', function (User $user): bool {
            // Only Admin/Proprietor can change which teacher teaches which
            // subject/class. Teachers can view their own through the
            // existing $user->assignedSubjects relation but cannot edit.
            return in_array($user->role, [
                UserRole::Admin->value,
                UserRole::Proprietor->value,
            ], true);
        });

        Gate::define('message.view', fn (User $user): bool => $user->can('viewAny', Message::class));
        Gate::define('message.create', fn (User $user): bool => $user->can('create', Message::class));
        Gate::define('message.manage', fn (User $user): bool => $user->can('manage', Message::class));
        Gate::define('message.sendFeeReminder', fn (User $user): bool => $user->can('sendFeeReminder', Message::class));
        Gate::define('message.sendClassNotice', fn (User $user): bool => $user->can('sendClassNotice', Message::class));

        Gate::define('viewPlatformNotices', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('sendPlatformNotice', fn (User $user): bool => $user->isSuperAdmin());
    }

    /**
     * Defence-in-depth login rate limiting. The login controller also tracks
     * failed attempts per email+IP, but the network-level limiter here blocks
     * IP-wide flooding regardless of the email guessed.
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email', '')));

            return Limit::perMinute(10)
                ->by(sha1($email).'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email', '')));

            return Limit::perMinute(5)
                ->by(sha1($email).'|'.$request->ip());
        });
    }
}
