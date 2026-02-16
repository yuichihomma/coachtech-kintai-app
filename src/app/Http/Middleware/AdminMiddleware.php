<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 未ログインなら admin/login へ
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        // admin 以外は拒否
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
