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
            $user = Auth::user();

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
                return redirect()->route('warehouse.raw_materials.index');
            }
            if ($user->isCashier()) {
                return redirect()->route('cashier.dashboard');
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => "Akun Anda memiliki role '{$user->role}' yang belum terdaftar untuk hak akses dashboard. Silakan hubungi Administrator.",
            ]);
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
            return redirect()->route('warehouse.raw_materials.index');
        }

        if ($user->isCashier()) {
            return redirect()->route('cashier.dashboard');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => "Akun Anda memiliki role '{$user->role}' yang belum memiliki akses dashboard.",
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
