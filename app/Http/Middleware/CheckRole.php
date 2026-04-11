<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next, ...$roles)
{
    // Cek apakah user sudah login
    if (!auth()->check()) {
        return redirect('login');
    }

    // Ambil kasta user (kepala_ppic, staff_ppic, atau produksi)
    $userRole = auth()->user()->role;

    // Jika role user ada dalam daftar yang diizinkan, maka boleh lewat
    if (in_array($userRole, $roles)) {
        return $next($request);
    }

    // Jika tidak punya akses, tendang ke dashboard dengan pesan error
    return redirect('/dashboard')->with('error', 'Akses Ditolak!.');
}
}
