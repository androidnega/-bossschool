<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnforceMaintenanceMode;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            EnforceTwoFactor::class,
        ]);

        $middleware->prepend(AssignRequestId::class);

        $middleware->api(prepend: [
            IdentifyTenant::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'tenant' => IdentifyTenant::class,
            'maintenance' => EnforceMaintenanceMode::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function (): string {
            $user = auth()->user();

            return $user ? $user->homeRoute() : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->context(function () {
            return [
                'request_id' => app()->bound('request.id') ? app('request.id') : null,
                'tenant_id' => optional(request()->user())->tenant_id,
                'user_id' => optional(request()->user())->id,
            ];
        });

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! app()->bound('request.id')) {
                return null;
            }
            $code = method_exists($e, 'getStatusCode') ? (int) $e->getStatusCode() : 500;
            if (in_array($code, [403, 404, 419, 429], true)) {
                return null; // let Laravel render errors/{code}.blade.php
            }

            // Production 500: render a friendly page with the request id.
            // Skip the friendly page when APP_DEBUG=true so operators can
            // see the real Whoops/Ignition trace while debugging on
            // production-tier environments.
            if ($code === 500
                && app()->environment('production')
                && ! config('app.debug')
                && ! $request->expectsJson()) {
                return response()->view('errors.500', [
                    'request_id' => app('request.id'),
                ], 500);
            }

            return null;
        });
    })->create();
