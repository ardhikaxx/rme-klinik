<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, AuthService $authService): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        $authService->login($request, $request->input('login'), $request->input('password'), $remember);

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.auth()->user()->name);
    }

    public function logout(Request $request, AuthService $authService): RedirectResponse
    {
        $authService->logout($request);

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    public function changePassword(Request $request, AuthService $authService): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authService->changePassword(auth()->user(), $request->input('current_password'), $request->input('new_password'));

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}
