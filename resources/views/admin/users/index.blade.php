@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Manajemen Pengguna (Akun)</h2>
            <p class="text-xs text-[#786e60] mt-0.5 font-sans">Kelola akun Admin, Pemilik Toko, dan Kasir yang memiliki akses ke sistem.</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-add-user').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition hover:scale-[1.02] active:scale-95">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Tambah Akun Pengguna
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#faf7f0] border-b border-[#e5dfd3] text-[#786e60] text-xs font-bold uppercase tracking-wider font-serif-classic">
                        <th class="py-3.5 px-6">Nama Pengguna</th>
                        <th class="py-3.5 px-6">Email / Username</th>
                        <th class="py-3.5 px-6 text-center">Peran (Role)</th>
                        <th class="py-3.5 px-6 text-center">Transaksi Dilayani</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9] font-medium">
                    @foreach($users as $user)
                        <tr class="hover:bg-[#faf7f0]/70 transition">
                            <td class="py-4 px-6 font-bold text-[#262017] flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $user->isOwner() ? 'bg-amber-100 text-[#8a681c] border border-amber-300' : ($user->isAdmin() ? 'bg-[#262017] text-[#f7f5f0]' : ($user->isKitchen() ? 'bg-blue-100 text-blue-800 border border-blue-300' : ($user->isWarehouse() ? 'bg-orange-100 text-orange-800 border border-orange-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'))) }} flex items-center justify-center font-bold text-xs shadow-2xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-serif-classic text-[#262017]">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="ml-1 text-[10px] text-[#8a681c] font-bold bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Anda</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-xs text-[#786e60] font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $user->isOwner() ? 'bg-amber-50 text-amber-800 border border-amber-300' : ($user->isAdmin() ? 'bg-[#f0ebd9] text-[#262017] border border-[#dcd4c5]' : ($user->isKitchen() ? 'bg-blue-50 text-blue-800 border border-blue-300' : ($user->isWarehouse() ? 'bg-orange-50 text-orange-800 border border-orange-300' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'))) }}">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-[#5c5346] text-xs">
                                {{ number_format($user->sales_count) }} transaksi
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-500 border border-stone-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Tombol Simbol: Ubah Data Pengguna (Pencil / Edit Icon) -->
                                    <button type="button"
                                        title="Ubah Data Pengguna"
                                        aria-label="Ubah Data Pengguna"
                                        onclick="openEditUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-800 hover:bg-[#c59b27] hover:text-stone-950 border border-amber-300/80 transition shadow-2xs hover:scale-105 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Simbol: Aktifkan / Nonaktifkan Akun (Toggle / Refresh Cycle Icon) -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                title="{{ $user->is_active ? 'Nonaktifkan Akun Pengguna' : 'Aktifkan Akun Pengguna' }}"
                                                aria-label="{{ $user->is_active ? 'Nonaktifkan Akun Pengguna' : 'Aktifkan Akun Pengguna' }}"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg {{ $user->is_active ? 'bg-stone-100 text-stone-700 hover:bg-stone-300 border border-stone-300' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-600 hover:text-white border border-emerald-300' }} transition shadow-2xs hover:scale-105 active:scale-95">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-[#f0ebd9] bg-[#faf7f0]">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah User -->
<div id="modal-add-user" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-[#e5dfd3] w-full max-w-md p-6">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <h3 class="font-serif-classic font-bold text-base text-[#262017]">Daftarkan Akun Baru</h3>
            <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Rian Pratama"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Email / Username *</label>
                <input type="email" name="email" required placeholder="rian@pos.test"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Peran Akses (Role) *</label>
                <select name="role" required class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="cashier" selected>Kasir (Melayani Transaksi & Pembayaran)</option>
                    <option value="warehouse">Staf Warehouse (Input Pengeluaran & Absensi)</option>
                    <option value="kitchen">Dapur (Mengelola Antrean Masakan KDS)</option>
                    <option value="admin">Administrator (Akses Manajemen Master Data & Sistem)</option>
                    <option value="owner">Pemilik Toko (Dashboard Eksekutif & Laporan Penjualan)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Kata Sandi (Password) *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#c59b27] hover:bg-[#a98119] text-stone-950 shadow-xs">
                    Buat Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah User -->
<div id="modal-edit-user" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-[#e5dfd3] w-full max-w-md p-6">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <h3 class="font-serif-classic font-bold text-base text-[#262017]">Ubah Data Pengguna</h3>
            <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-edit-user" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama Lengkap *</label>
                <input type="text" id="edit-user-name" name="name" required
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Email / Username *</label>
                <input type="email" id="edit-user-email" name="email" required
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Peran Akses (Role) *</label>
                <select id="edit-user-role" name="role" required class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="cashier">Kasir</option>
                    <option value="warehouse">Staf Warehouse</option>
                    <option value="kitchen">Dapur</option>
                    <option value="admin">Administrator</option>
                    <option value="owner">Pemilik Toko</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tetap"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] shadow-xs">
                    Perbarui Akun
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUser(id, name, email, role) {
        document.getElementById('form-edit-user').action = '/admin/users/' + id;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-email').value = email;
        document.getElementById('edit-user-role').value = role;
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
</script>
@endsection
