<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function simple(HealthCheckService $service): JsonResponse
    {
        $result = $service->simple();
        $status = $result['status'] === 'ok' ? 200 : 503;

        return response()->json($result, $status);
    }
}
