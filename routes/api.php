<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Web\PaymentWebhookController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

/*
| Public webhook for Ghana Mobile Money gateway callbacks. Unsigned /
| invalid requests are rejected with 401 by the controller. No tenant
| middleware: callbacks arrive before we know which tenant they belong
| to (the controller derives that from the verified payload).
*/
Route::post('/webhooks/payments/{provider}', [PaymentWebhookController::class, 'handle'])
    ->withoutMiddleware([IdentifyTenant::class])
    ->middleware('throttle:60,1')
    ->name('webhooks.payments');

/*
|--------------------------------------------------------------------------
| Future: Platform API (SuperAdmin + explicit tenant scope)
|--------------------------------------------------------------------------
| TODO: GET  /api/platform/tenants/{tenant}/students
| TODO: GET  /api/platform/tenants/{tenant}/students/{student}
| TODO: GET  /api/platform/tenants/{tenant}/students/{student}/results
| TODO: GET  /api/platform/tenants/{tenant}/students/{student}/attendance
| TODO: GET  /api/platform/tenants/{tenant}/students/{student}/fee-status
| Controllers: App\Http\Controllers\Api\Platform\* — require Sanctum + SuperAdmin + tenant id validation.
*/
