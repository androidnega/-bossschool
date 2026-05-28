<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\ProductionChecklistService;
use Illuminate\View\View;

class ProductionChecklistController extends Controller
{
    public function index(ProductionChecklistService $service): View
    {
        $this->authorize('platform.view');
        $summary = $service->summary();

        return view('platform.production_checklist.index', ['summary' => $summary]);
    }
}
