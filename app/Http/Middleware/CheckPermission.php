<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.']);
        }

        // Check if user has permission through any assigned role
        if (! $user->hasPermission($permission)) {
            abort(403, "Akses ditolak: Anda tidak memiliki izin [{$permission}] untuk melakukan tindakan ini.");
        }

        return $next($request);
    }
}
