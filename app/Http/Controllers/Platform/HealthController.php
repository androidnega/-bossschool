<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\View\View;

class HealthController extends Controller
{
    public function detailed(HealthCheckService $service): View
    {
        $this->authorize('platform.view');

        $result = $service->detailed();

        return view('platform.health.index', ['result' => $result]);
    }
}
