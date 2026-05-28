<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationLogController extends Controller
{
    public function index(Request $request): View
    {
        // Only finance & admin staff. Teachers cannot view fee/SMS history.
        abort_unless($request->user()->isFinanceRole(), 403);

        $query = CommunicationLog::query()
            ->with(['recipient', 'creator'])
            ->orderByDesc('id');

        if ($channel = $request->query('channel')) {
            $query->where('channel', $channel);
        }
        if ($purpose = $request->query('purpose')) {
            $query->where('purpose', $purpose);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('communication-logs.index', compact('logs'));
    }
}
