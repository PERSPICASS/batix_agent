<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GrowthAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('growth_admin_id');
        $authenticated = is_int($adminId)
            && AdminUser::query()->whereKey($adminId)->where('is_active', true)->exists();

        if (! $authenticated) {
            $request->session()->forget('growth_admin_id');

            return redirect()->guest(route('growth.login'));
        }

        return $next($request);
    }
}
