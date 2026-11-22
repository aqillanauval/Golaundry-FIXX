<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::user()->role != $role) {
            return redirect()->route('dashboard')
                ->with('error', 'Kamu tidak punya akses ke halaman itu.');
        }

        return $next($request);
    }
}
