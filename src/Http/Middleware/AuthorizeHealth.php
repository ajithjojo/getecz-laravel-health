<?php

namespace Getecz\LaravelHealth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeHealth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('getecz-health.enabled', true)) {
            abort(404);
        }

        // Optional IP allowlist
        $allowedIps = config('getecz-health.allowed_ips', []);
        if (!empty($allowedIps)) {
            $ip = $request->ip();
            if (!in_array($ip, $allowedIps, true)) {
                abort(403, 'IP not allowed');
            }
        }

        // Optional token
        $token = config('getecz-health.token');
        if ($token) {
            $provided = $request->header('X-Getecz-Health-Token') ?? $request->query('token');
            if (!is_string($provided) || !hash_equals((string) $token, (string) $provided)) {
                abort(403, 'Invalid token');
            }
        }

        return $next($request);
    }
}
