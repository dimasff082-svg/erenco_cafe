@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Manajemen Data Meja Restoran</h2>
            <p class="text-xs text-[#786e60] mt-0.5 font-sans">Kelola nomor meja, pantau status penggunaan meja, dan cetak barcode QR Code pemesanan mandiri.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddTableModal()"
                class="inline-flex items-center gap-2 bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition hover:scale-[1.02] active:scale-95">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Meja Baru
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 text-xs text-emerald-950 rounded-2xl bg-emerald-50 border border-emerald-300 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 text-xs text-rose-950 rounded-2xl bg-rose-50 border border-rose-300 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Kartu Statistik Meja -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-[#e5dfd3] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#786e60] uppercase tracking-wider block font-serif-classic">Total Meja Resto</span>
                <div class="text-2xl font-black text-[#262017] tracking-tight mt-1">{{ $stats['total'] }}</div>
                <p class="text-[11px] text-stone-400 mt-0.5">Meja terdaftar di sistem</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#e5dfd3] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#786e60] uppercase tracking-wider block font-serif-classic">Tersedia (Kosong)</span>
                <div class="text-2xl font-black text-emerald-600 tracking-tight mt-1">{{ $stats['available'] }}</div>
                <p class="text-[11px] text-stone-400 mt-0.5">Siap digunakan pelanggan</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#e5dfd3] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#786e60] uppercase tracking-wider block font-serif-classic">Terisi (Occupied)</span>
                <div class="text-2xl font-black text-amber-600 tracking-tight mt-1">{{ $stats['occupied'] }}</div>
                <p class="text-[11px] text-stone-400 mt-0.5">Sedang ada pelanggan aktif</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Meja -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-xs p-4">
        <form action="{{ route('admin.tables.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari Kode Meja (misal: M001) atau Keterangan Meja..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#5c5346] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="all">Semua Status Meja</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia (Kosong)</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Terisi (Occupied)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1.5">
                <button type="submit" class="flex-1 bg-[#262017] hover:bg-[#15120c] text-white text-xs font-bold px-3 py-2 rounded-xl transition shadow-xs">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.tables.index') }}" class="bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Meja -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#faf7f0] border-b border-[#e5dfd3] text-[#786e60] text-xs font-bold uppercase tracking-wider font-serif-classic">
                        <th class="py-3.5 px-6">Nomor / Kode Meja</th>
                        <th class="py-3.5 px-6">Keterangan / Lokasi</th>
                        <th class="py-3.5 px-6 text-center">Status Meja</th>
                        <th class="py-3.5 px-6 text-center">Sesi / Pesanan</th>
                        <th class="py-3.5 px-6 text-center">Barcode / QR Code</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9] font-medium">
                    @forelse($tables as $table)
                        @php
                            $orderUrl = route('order.index', $table->table_number);
                        @endphp
                        <tr class="hover:bg-[#faf7f0]/70 transition">
                            <!-- Nomor / Kode Meja -->
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm font-extrabold text-[#262017] bg-[#f0ebd9] px-2.5 py-1 rounded-xl border border-[#dcd4c5]">
                                    {{ $table->table_number }}
                                </span>
                            </td>

                            <!-- Keterangan / Nama Meja -->
                            <td class="py-4 px-6 text-xs text-[#5c5346] font-semibold">
                                {{ $table->name }}
                            </td>

                            <!-- Status Meja -->
                            <td class="py-4 px-6 text-center">
                                @if($table->status === 'occupied')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Terisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @endif
                            </td>

                            <!-- Sesi / Pesanan -->
                            <td class="py-4 px-6 text-center text-xs">
                                @if($table->activeOpenBill)
                                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 font-bold text-[11px] block">
                                        Open Bill #{{ $table->activeOpenBill->bill_number }}
                                    </span>
                                @elseif($table->active_orders_count > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-900 font-bold text-[11px] block">
                                        {{ $table->active_orders_count }} Pesanan Belum Bayar
                                    </span>
                                @else
                                    <span class="text-stone-400 text-xs">Kosong</span>
                                @endif
                            </td>

                            <!-- Barcode / QR Code Preview & Cetak -->
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.tables.print_qr', $table->id) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 hover:bg-[#c59b27] hover:text-stone-950 text-amber-900 border border-amber-300 text-xs font-bold transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        Cetak Barcode
                                    </a>
                                </div>
                            </td>

                            <!-- Aksi (Edit & Hapus) -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Tombol Preview Link Pesan -->
                                    <a href="{{ route('order.index', $table->table_number) }}" target="_blank"
                                        title="Buka Halaman Pemesanan Meja"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 transition shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>

                                    <!-- Tombol Edit Meja -->
                                    <button type="button"
                                        onclick="openEditTableModal({{ $table->id }}, '{{ addslashes($table->table_number) }}', '{{ addslashes($table->name) }}', '{{ $table->status }}')"
                                        title="Ubah Data Meja"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-800 hover:bg-[#c59b27] hover:text-stone-950 border border-amber-300 transition shadow-2xs hover:scale-105 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Hapus Meja -->
                                    <form action="{{ route('admin.tables.destroy', $table->id) }}" method="POST" class="inline"
                                        data-confirm="Apakah Anda yakin ingin menghapus Meja {{ $table->table_number }}? Seluruh riwayat QR Code dan pesanan terkait meja ini akan terpengaruh."
                                        data-confirm-title="Hapus Meja Resto"
                                        data-confirm-btn="Ya, Hapus Meja"
                                        data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            title="Hapus Meja"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white border border-rose-300 transition shadow-2xs hover:scale-105 active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-[#786e60] text-xs">
                                <svg class="w-12 h-12 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="font-bold text-sm text-[#262017]">Belum ada meja restoran yang terdaftar.</p>
                                <p class="text-[11px] text-stone-400 mt-1">Klik tombol "Tambah Meja Baru" di atas untuk menambahkan meja pertama.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tables->hasPages())
            <div class="p-4 border-t border-[#f0ebd9] bg-[#faf7f0]">
                {{ $tables->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Meja Baru -->
<div id="modal-add-table" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-[#e5dfd3] w-full max-w-md p-6">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <h3 class="font-serif-classic font-bold text-base text-[#262017]">Tambah Meja Baru</h3>
            <button type="button" onclick="closeAddTableModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('admin.tables.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nomor / Kode Meja *</label>
                <input type="text" name="table_number" required placeholder="Contoh: M009 atau VIP-01"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm font-mono font-bold text-[#262017] uppercase focus:ring-2 focus:ring-amber-500 focus:outline-none">
                <span class="text-[10px] text-stone-400 mt-0.5 block">Kode unik meja yang akan menjadi rute URL scan QR (misal: /order/M009).</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama / Keterangan Meja *</label>
                <input type="text" name="name" required placeholder="Contoh: Meja 09 (Outdoor / Lantai 2)"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Status Awal Meja *</label>
                <select name="status" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="available" selected>Tersedia (Siap Digunakan Pelanggan)</option>
                    <option value="occupied">Terisi (Sedang Ada Pelanggan)</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeAddTableModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#c59b27] hover:bg-[#a98119] text-stone-950 shadow-xs">
                    Simpan Meja
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Data Meja -->
<div id="modal-edit-table" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-[#e5dfd3] w-full max-w-md p-6">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <h3 class="font-serif-classic font-bold text-base text-[#262017]">Ubah Data Meja</h3>
            <button type="button" onclick="closeEditTableModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-edit-table" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nomor / Kode Meja *</label>
                <input type="text" id="edit-table-number" name="table_number" required
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm font-mono font-bold text-[#262017] uppercase focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama / Keterangan Meja *</label>
                <input type="text" id="edit-table-name" name="name" required
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Status Meja *</label>
                <select id="edit-table-status" name="status" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-sm text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="available">Tersedia (Kosong)</option>
                    <option value="occupied">Terisi (Occupied)</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeEditTableModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] shadow-xs">
                    Perbarui Meja
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddTableModal() {
        document.getElementById('modal-add-table').classList.remove('hidden');
    }

    function closeAddTableModal() {
        document.getElementById('modal-add-table').classList.add('hidden');
    }

    function openEditTableModal(id, tableNumber, name, status) {
        document.getElementById('form-edit-table').action = '/admin/tables/' + id;
        document.getElementById('edit-table-number').value = tableNumber;
        document.getElementById('edit-table-name').value = name;
        document.getElementById('edit-table-status').value = status;
        document.getElementById('modal-edit-table').classList.remove('hidden');
    }

    function closeEditTableModal() {
        document.getElementById('modal-edit-table').classList.add('hidden');
    }
</script>
@endsection

