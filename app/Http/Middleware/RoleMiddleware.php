<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            // Redirect based on role
            if ($user->role === 'student') {
                return redirect()->route('student.dashboard')->with('error', 'Akses dibatasi untuk pelajar.');
            }

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ini khusus Super Admin.');
            }

            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
