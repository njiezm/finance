<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAppUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('app_unlocked') === true) {
            return redirect()->route('overview');
        }

        return $next($request);
    }
}
