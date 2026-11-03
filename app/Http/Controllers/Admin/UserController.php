<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('sales')->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,cashier,owner,kitchen,warehouse'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil didaftarkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:admin,cashier,owner,kitchen,warehouse'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang login.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->name} berhasil {$status}.");
    }
}
