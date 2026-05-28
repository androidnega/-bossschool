<?php

namespace App\Http\Middleware;

use App\Models\MaintenanceMode;
use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceMaintenanceMode
{
    /**
     * Block tenant users when global or per-tenant maintenance is active. SuperAdmin bypasses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('platform.*')) {
            return $next($request);
        }

        if ($request->routeIs('login', 'logout') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        if ($request->is('/')) {
            return $next($request);
        }

        $global = MaintenanceMode::isGlobalEnabled()
            || PlatformSetting::getBool('maintenance_enabled', false);

        if ($global) {
            return $this->maintenanceResponse($request, MaintenanceMode::globalRow(), true);
        }

        if ($user->tenant_id !== null && MaintenanceMode::isTenantEnabled((int) $user->tenant_id)) {
            $row = MaintenanceMode::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('is_enabled', true)
                ->orderByDesc('id')
                ->first();

            return $this->maintenanceResponse($request, $row, false);
        }

        return $next($request);
    }

    private function maintenanceResponse(Request $request, ?MaintenanceMode $row, bool $global): Response
    {
        $message = $row?->message
            ?? PlatformSetting::getValue('maintenance_message')
            ?? __('We are performing scheduled maintenance. Please try again soon.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 503);
        }

        return response()
            ->view('maintenance', [
                'message' => $message,
                'global' => $global,
            ], 503);
    }
}
