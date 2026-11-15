<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user with username or email and password.
     */
    public function login(Request $request, string $login, string $password, bool $remember = false): User
    {
        $throttleKey = Str::transliterate(Str::lower($login).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLog::log('LOGIN_THROTTLED', 'AUTH', "Percobaan login terlalu sering untuk login: {$login}");
            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $login,
            'password' => $password,
        ];

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 300); // lockout for 5 mins after 5 attempts
            AuditLog::log('LOGIN_FAILED', 'AUTH', "Login gagal untuk kredensial: {$login}");
            throw ValidationException::withMessages([
                'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            AuditLog::log('LOGIN_BLOCKED', 'AUTH', "Percobaan login akun nonaktif: {$user->username}");
            throw ValidationException::withMessages([
                'login' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditLog::log('LOGIN_SUCCESS', 'AUTH', "Pengguna {$user->name} ({$user->username}) berhasil masuk");

        return $user;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): void
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::log('LOGOUT', 'AUTH', "Pengguna {$user->name} ({$user->username}) telah keluar");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Change user password with security verification.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini salah.',
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        AuditLog::log('PASSWORD_CHANGED', 'AUTH', "Pengguna {$user->name} berhasil mengubah password");
    }
}
