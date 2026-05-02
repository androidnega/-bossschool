<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roleChunks): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $allowed = [];

        foreach ($roleChunks as $chunk) {
            foreach (array_map('trim', explode(',', $chunk)) as $role) {
                if ($role !== '') {
                    $allowed[] = $role;
                }
            }
        }

        $allowed = array_unique($allowed);

        if ($allowed === [] || ! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
