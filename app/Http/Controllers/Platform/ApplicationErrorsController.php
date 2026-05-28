<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Last-resort "is anything on fire?" page for SuperAdmin. Reads the
 * recent tail of storage/logs/laravel.log so the operator can spot panic
 * patterns without SSH.
 *
 * If the project later wires Sentry/Bugsnag, this controller can be
 * replaced with a richer dashboard.
 */
class ApplicationErrorsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('platform.manageTenants');

        $path = storage_path('logs/laravel.log');
        $lines = [];
        if (is_file($path) && is_readable($path)) {
            $size = filesize($path);
            $read = min($size, 256 * 1024); // last ~256 KB
            $fh = fopen($path, 'r');
            fseek($fh, -1 * $read, SEEK_END);
            $blob = fread($fh, $read);
            fclose($fh);
            $lines = array_slice(explode("\n", trim((string) $blob)), -300);
        }

        $entries = [];
        $needle = strtolower((string) $request->query('q', ''));
        foreach (array_reverse($lines) as $line) {
            if ($needle !== '' && ! str_contains(strtolower($line), $needle)) {
                continue;
            }
            $entries[] = $line;
            if (count($entries) >= 200) {
                break;
            }
        }

        return view('platform.errors.index', [
            'entries' => $entries,
            'query' => $request->query('q'),
        ]);
    }
}
