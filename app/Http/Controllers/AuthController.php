<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::user()->isOwner()) {
                return redirect()->route('owner.dashboard');
            }
            if (Auth::user()->isKitchen()) {
                return redirect()->route('kitchen.index');
            }
            if (Auth::user()->isWarehouse()) {
                return redirect()->route('warehouse.expenses.index');
            }

            return redirect()->route('cashier.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isOwner()) {
            return redirect()->route('owner.dashboard');
        }

        if ($user->isKitchen()) {
            return redirect()->route('kitchen.index');
        }

        if ($user->isWarehouse()) {
            return redirect()->route('warehouse.expenses.index');
        }

        return redirect()->route('cashier.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
