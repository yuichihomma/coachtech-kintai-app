<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) //ログインしていて、かつ role が staff の人だけ通して、それ以外は 403 エラー
    {
        if (auth()->check() && auth()->user()->role === 'staff') {
            return $next($request);
        }

        abort(403);
    }
}
