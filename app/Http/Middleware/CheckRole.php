<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  // <-- Tambahkan parameter $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user sudah login
        // 2. Cek apakah role user SAMA DENGAN role yang diizinkan ($role)
        if (!Auth::check() || Auth::user()->role !== $role) {

            // Jika tidak sesuai, lempar ke halaman home atau dashboard
            // Anda bisa ganti '/' dengan rute lain, misal 'dashboard'
            return redirect('/')->with('error', 'Anda tidak punya hak akses.');
        }

        // Jika sesuai, lanjutkan ke halaman yang dituju
        return $next($request);
    }
}
