<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'user') {
            // Hindari loop jika sudah di dashboard
            if ($request->routeIs('dashboard')) {
                abort(403, 'Unauthorized access.');
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
