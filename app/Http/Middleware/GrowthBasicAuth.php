<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GrowthBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = (string) env('ADMIN_USER');
        $expectedPassword = (string) env('ADMIN_PASSWORD');

        if ($expectedUser === '' || $expectedPassword === '') {
            abort(503, 'ADMIN_USER / ADMIN_PASSWORD must be configured.');
        }

        $valid = hash_equals($expectedUser, (string) $request->getUser())
            && hash_equals($expectedPassword, (string) $request->getPassword());

        if (!$valid) {
            return response('Authentification requise.', 401, [
                'WWW-Authenticate' => 'Basic realm="BATIX Growth"',
            ]);
        }

        return $next($request);
    }
}
