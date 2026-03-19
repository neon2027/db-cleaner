<?php

namespace Laravelldone\DbCleaner\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DbCleanerApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('db-cleaner.api.auth_token');

        // If no token configured, allow all (suitable for local dev)
        if (! $token) {
            return $next($request);
        }

        $provided = $request->bearerToken() ?? $request->header('X-DbCleaner-Token');

        if (! $provided || ! hash_equals($token, $provided)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
