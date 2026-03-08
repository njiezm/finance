<?php

namespace App\Http\Middleware;

use App\Services\AppLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppLockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('app_unlocked') === true) {
            return $next($request);
        }

        $cookieToken = (string) $request->cookie('fp_remember', '');
        $expectedToken = AppLockService::rememberToken($request);

        if ($cookieToken !== '' && hash_equals($expectedToken, $cookieToken)) {
            $request->session()->put('app_unlocked', true);

            return $next($request);
        }

        return redirect()->route('auth.login');
    }
}
