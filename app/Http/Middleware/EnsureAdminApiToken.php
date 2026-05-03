<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.admin_api.token');

        if (! is_string($configuredToken) || trim($configuredToken) === '') {
            abort(403, 'Admin API token is not configured.');
        }

        $providedToken = $request->bearerToken() ?: $request->header('X-Admin-Token');

        if (! is_string($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            abort(403, 'Invalid admin API token.');
        }

        return $next($request);
    }
}
