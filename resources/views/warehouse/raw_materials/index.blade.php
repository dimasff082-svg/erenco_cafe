@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Inventori Bahan Baku</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Manajemen stok fisik bahan baku minuman, dairy, sirup, bubuk, dan packaging cafe.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('warehouse.raw_materials.mutations') }}"
                class="inline-flex items-center gap-2 bg-[#fcfaf5] hover:bg-[#ede7da] text-[#5c5346] text-xs font-bold px-3.5 py-2.5 rounded-xl border border-[#dcd4c5] shadow-2xs transition active:scale-95">
                <svg class="w-4 h-4 text-[#8f8573]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Buku Mutasi Stok</span>
            </a>
            <button type="button" onclick="openAddModal()"
                class="inline-flex items-center gap-2 bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Tambah Bahan Baku</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs shadow-2xs space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terjadi kendala pada penginputan:</span>
            </div>
            <ul class="list-disc list-inside pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4 Kartu KPI Ringkasan Inventori -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Item -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Total Master Bahan</span>
                <span class="text-2xl font-black text-[#262017] mt-0.5 block">{{ $summary['total_items'] }} <span class="text-xs font-semibold text-[#8f8573]">Item</span></span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">Tercatat di katalog sistem</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-stone-100 border border-[#e5dfd3] flex items-center justify-center text-[#5c5346] shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Total Nilai Aset Bahan Baku -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Estimasi Nilai Persediaan</span>
                <span class="text-xl font-black text-amber-800 mt-0.5 block">Rp {{ number_format($summary['total_asset_value'], 0, ',', '.') }}</span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">Kuantitas × Harga Beli Satuan</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#c59b27] shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Peringatan Stok Menipis -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">Peringatan Stok Menipis</span>
                <span class="text-2xl font-black text-amber-600 mt-0.5 block">{{ $summary['low_stock_count'] }} <span class="text-xs font-semibold text-[#8f8573]">Item</span></span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">&le; batas minimum stok</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-300 flex items-center justify-center text-amber-600 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Peringatan Stok Habis -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Peringatan Stok Habis</span>
                <span class="text-2xl font-black text-rose-600 mt-0.5 block">{{ $summary['out_of_stock_count'] }} <span class="text-xs font-semibold text-[#8f8573]">Item</span></span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">Saldo fisik &le; 0 (Kosong)</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-300 flex items-center justify-center text-rose-600 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs">
        <form action="{{ route('warehouse.raw_materials.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode (BHN-001), nama bahan, atau kategori..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="category" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="safe" {{ request('status') === 'safe' ? 'selected' : '' }}>Stok Aman</option>
                    <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full bg-[#262017] hover:bg-stone-800 text-white font-bold py-2 px-3 rounded-xl text-xs transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('warehouse.raw_materials.index') }}" class="p-2 text-stone-500 hover:text-stone-800 rounded-xl hover:bg-stone-100" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Master Bahan Baku -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#e5dfd3] bg-[#faf7f0] text-[#5c5346] font-serif-classic font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Bahan Baku & Kategori</th>
                        <th class="py-3 px-4 text-center">Satuan</th>
                        <th class="py-3 px-4 text-right">Sisa Stok Fisik</th>
                        <th class="py-3 px-4 text-right">Batas Min.</th>
                        <th class="py-3 px-4 text-right">Harga Beli Satuan</th>
                        <th class="py-3 px-4 text-right">Nilai Aset</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9]">
                    @forelse($rawMaterials as $item)
                        @php
                            $badge = $item->statusBadge();
                        @endphp
                        <tr class="hover:bg-[#faf7f0]/60 transition">
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-[#262017]">{{ $item->code }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-[#262017] text-xs">{{ $item->name }}</div>
                                <div class="text-[10px] text-[#8f8573] flex items-center gap-2 mt-0.5">
                                    <span class="inline-block px-1.5 py-0.2 rounded bg-stone-100 text-stone-700 font-medium">{{ $item->category }}</span>
                                    @if($item->notes)
                                        <span class="truncate max-w-[200px]" title="{{ $item->notes }}">{{ $item->notes }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-[11px] text-[#5c5346]">
                                {{ $item->unit }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-sm {{ $item->isOutOfStock() ? 'text-rose-600' : ($item->isLowStock() ? 'text-amber-600' : 'text-emerald-700') }}">
                                {{ number_format($item->current_stock, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-stone-500">
                                {{ number_format($item->min_stock, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-stone-800">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-amber-900">
                                Rp {{ number_format($item->totalAssetValue(), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Restock Cepat (+) -->
                                    <button type="button" onclick="openRestockModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->unit_price }})"
                                        class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition" title="Restock Masuk (+)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>

                                    <!-- Catat Pemakaian Bar/Rusak (-) -->
                                    <button type="button" onclick="openUsageModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->current_stock }})"
                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition" title="Catat Pemakaian / Rusak (-)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                        </svg>
                                    </button>

                                    <!-- Stock Opname Fisik -->
                                    <button type="button" onclick="openOpnameModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->current_stock }})"
                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition" title="Stock Opname Fisik">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </button>

                                    <!-- Kartu Riwayat Mutasi Item -->
                                    <a href="{{ route('warehouse.raw_materials.history', $item->id) }}"
                                        class="p-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-[#5c5346] transition" title="Lihat Kartu Stok">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </a>

                                    <!-- Edit Master Data -->
                                    <button type="button" onclick="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 rounded-lg hover:bg-stone-100 text-stone-500 hover:text-stone-800 transition" title="Edit Master Bahan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-[#8f8573]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="text-sm font-bold text-[#262017]">Belum Ada Data Bahan Baku</p>
                                <p class="text-xs text-[#8f8573] mt-0.5">Klik tombol "Tambah Bahan Baku" untuk menambahkan item baru ke inventori.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rawMaterials->hasPages())
            <div class="p-4 border-t border-[#e5dfd3] bg-[#faf7f0]">
                {{ $rawMaterials->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Modal Tambah Bahan Baku Baru -->
<div id="modal-add-material" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-lg p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-[#262017]">Tambah Master Bahan Baku</h3>
                <p class="text-[11px] text-[#786e60]">Daftarkan bahan baku baru ke sistem inventori kafe.</p>
            </div>
            <button type="button" onclick="closeAddModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('warehouse.raw_materials.store') }}" method="POST" class="space-y-3.5">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <!-- Kode Bahan -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Kode Bahan (Opsional)</label>
                    <input type="text" name="code" placeholder="Auto: BHN-xxx"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Kategori *</label>
                    <input type="text" name="category" required list="categoryList" placeholder="Contoh: Biji Kopi"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <datalist id="categoryList">
                        <option value="Biji Kopi">
                        <option value="Susu & Dairy">
                        <option value="Sirup & Pemanis">
                        <option value="Bubuk Minuman">
                        <option value="Packaging & Cup">
                        <option value="Topping & Isian">
                        <option value="Bahan Masakan">
                    </datalist>
                </div>
            </div>

            <!-- Nama Bahan Baku -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama Bahan Baku *</label>
                <input type="text" name="name" required placeholder="Contoh: Biji Kopi Arabika House Blend"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <!-- Satuan Ukuran -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Satuan *</label>
                    <select name="unit" required class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                        <option value="gram">gram (gr)</option>
                        <option value="liter">liter (L)</option>
                        <option value="ml">mililiter (ml)</option>
                        <option value="pcs" selected>pcs / biji</option>
                        <option value="kaleng">kaleng</option>
                        <option value="botol">botol</option>
                        <option value="pack">pack</option>
                        <option value="kg">kilogram (kg)</option>
                    </select>
                </div>

                <!-- Saldo Stok Awal -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Stok Awal</label>
                    <input type="number" step="0.01" min="0" name="initial_stock" value="0"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Batas Minimum Stok -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Batas Min. *</label>
                    <input type="number" step="0.01" min="0" name="min_stock" value="5" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>
            </div>

            <!-- Harga Beli Satuan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Harga Beli per Satuan (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2 text-xs font-bold text-[#8f8573]">Rp</span>
                    <input type="text" id="addMaterialPrice" placeholder="0" oninput="formatRupiahInput(this)"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl pl-10 pr-3.5 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <input type="hidden" name="unit_price" id="addMaterialPriceRaw" value="0">
                </div>
                <p class="text-[10px] text-stone-400 mt-0.5">Digunakan untuk menghitung nilai valuasi aset stok di gudang.</p>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Catatan / Spesifikasi (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Spesifikasi merek, asal biji kopi, rasa sirup..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#c59b27] hover:bg-[#a98119] text-stone-950 shadow-xs">
                    Simpan Bahan Baku
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Restock Cepat (+) -->
<div id="modal-restock" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-md p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-emerald-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-black">+</span>
                    Restock Bahan Masuk
                </h3>
                <p id="restockItemName" class="text-xs font-bold text-[#262017] mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeRestockModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form id="formRestock" method="POST" class="space-y-3.5">
            @csrf

            <!-- Jumlah Masuk -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Jumlah Masuk (<span id="restockUnitLabel">satuan</span>) *</label>
                <input type="number" step="0.01" min="0.01" name="quantity" required placeholder="Contoh: 1000"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <!-- Perbarui Harga Beli (Opsional) -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Harga Beli Satuan Baru (Opsional)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2 text-xs font-bold text-[#8f8573]">Rp</span>
                    <input type="text" id="restockPriceInput" oninput="formatRupiahInput(this, 'restockPriceRaw')" placeholder="Kosongkan jika harga tetap"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl pl-10 pr-3.5 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <input type="hidden" name="unit_price" id="restockPriceRaw">
                </div>
            </div>

            <!-- No Referensi / Nota -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">No. Referensi / Nota (Opsional)</label>
                <input type="text" name="reference_number" placeholder="Contoh: NOTA-SUPPLIER-019"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Keterangan / Catatan</label>
                <textarea name="notes" rows="2" placeholder="Restock belanja dari supplier..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeRestockModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
                    Tambah Stok Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Catat Pemakaian Bar/Rusak (-) -->
<div id="modal-usage" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-md p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-rose-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-black">-</span>
                    Catat Pengeluaran Bahan
                </h3>
                <p id="usageItemName" class="text-xs font-bold text-[#262017] mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeUsageModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form id="formUsage" method="POST" class="space-y-3.5">
            @csrf

            <!-- Info Sisa Stok -->
            <div class="p-2.5 rounded-xl bg-stone-100 text-stone-800 text-xs flex items-center justify-between">
                <span class="font-medium text-[#786e60]">Sisa Stok Fisik Saat Ini:</span>
                <span id="usageCurrentStockDisplay" class="font-black font-mono text-[#262017]">0</span>
            </div>

            <!-- Tipe Pengeluaran Bahan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Alasan Pengeluaran *</label>
                <select name="usage_type" required class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="bar_usage" selected>Pemakaian Bar (Kopi & Minuman)</option>
                    <option value="kitchen_usage">Pemakaian Dapur (Masakan & Makanan)</option>
                    <option value="damaged">Bahan Tumpah / Pecah / Rusak (Waste)</option>
                    <option value="spoilage">Bahan Basi / Kedaluwarsa</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>

            <!-- Jumlah Keluar -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Jumlah Pengeluaran (<span id="usageUnitLabel">satuan</span>) *</label>
                <input type="number" step="0.01" min="0.01" name="quantity" id="usageQtyInput" required placeholder="Contoh: 250"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                <p id="usageStockWarning" class="text-[10px] text-rose-600 font-bold mt-1 hidden">Peringatan: Jumlah melebihi stok yang tersedia!</p>
            </div>

            <!-- Keterangan / Rincian Wajib -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Catatan / Rincian Pemakaian *</label>
                <textarea name="notes" required rows="2" placeholder="Contoh: Pemakaian shift pagi / 1 kaleng susu tumpah di bar..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeUsageModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" id="btnSubmitUsage" class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
                    Catat Pengurangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal Stock Opname Fisik -->
<div id="modal-opname" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-md p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-amber-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black">&plusmn;</span>
                    Stock Opname Fisik
                </h3>
                <p id="opnameItemName" class="text-xs font-bold text-[#262017] mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeOpnameModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form id="formOpname" method="POST" class="space-y-3.5">
            @csrf

            <!-- Perbandingan Stok Sistem vs Fisik -->
            <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3]">
                <div>
                    <span class="text-[10px] font-bold text-[#8f8573] uppercase tracking-wider block">Stok Sistem</span>
                    <span id="opnameSystemStockDisplay" class="font-mono font-bold text-sm text-[#262017] block mt-0.5">0</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#8f8573] uppercase tracking-wider block">Selisih (+/-)</span>
                    <span id="opnameDiffDisplay" class="font-mono font-black text-sm text-stone-600 block mt-0.5">0.00</span>
                </div>
            </div>

            <!-- Input Hitungan Fisik Riil -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Hasil Hitungan Fisik Riil (<span id="opnameUnitLabel">satuan</span>) *</label>
                <input type="number" step="0.01" min="0" name="actual_stock" id="opnameActualInput" required oninput="calculateOpnameDiff()"
                    class="w-full bg-white border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] font-black focus:ring-2 focus:ring-amber-500 focus:outline-none">
                <p class="text-[10px] text-stone-400 mt-0.5">Sistem akan otomatis menyesuaikan saldo stok sesuai angka fisik ini.</p>
            </div>

            <!-- Catatan Audit Opname -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Keterangan / Catatan Audit</label>
                <textarea name="notes" rows="2" placeholder="Hasil audit fisik akhir bulan / penyesuaian selisih..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="closeOpnameModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs">
                    Simpan Penyesuaian Opname
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal Edit Master Bahan Baku -->
<div id="modal-edit-material" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-lg p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-[#262017]">Edit Data Bahan Baku</h3>
                <p id="editMaterialCodeDisplay" class="text-[11px] font-mono font-bold text-amber-800">BHN-xxx</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form id="formEditMaterial" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <!-- Nama Bahan -->
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama Bahan Baku *</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Kategori *</label>
                    <input type="text" name="category" id="editCategory" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Satuan -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Satuan *</label>
                    <input type="text" name="unit" id="editUnit" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Batas Min -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Batas Min. Stok *</label>
                    <input type="number" step="0.01" min="0" name="min_stock" id="editMinStock" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Harga Beli -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Harga Beli per Satuan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs font-bold text-[#8f8573]">Rp</span>
                        <input type="text" id="editPriceDisplay" oninput="formatRupiahInput(this, 'editPriceRaw')"
                            class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl pl-9 pr-3 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                        <input type="hidden" name="unit_price" id="editPriceRaw">
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Catatan / Spesifikasi</label>
                <textarea name="notes" id="editNotes" rows="2"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none"></textarea>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="confirmDeleteMaterial()" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                    Hapus Bahan
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#c59b27] hover:bg-[#a98119] text-stone-950 shadow-xs">
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>

        <form id="formDeleteMaterial" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    // Format angka titik ribuan Rupiah
    function formatRupiahInput(input, targetHiddenId) {
        let clean = input.value.replace(/\D/g, '');
        let target = targetHiddenId ? document.getElementById(targetHiddenId) : null;
        if (target) {
            target.value = clean;
        }
        if (clean === '') {
            input.value = '';
            return;
        }
        input.value = new Intl.NumberFormat('id-ID').format(clean);
    }

    // Modal Tambah
    function openAddModal() {
        document.getElementById('modal-add-material').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('modal-add-material').classList.add('hidden');
    }

    // Modal Restock
    function openRestockModal(id, name, unit, price) {
        document.getElementById('formRestock').action = `/warehouse/raw-materials/${id}/restock`;
        document.getElementById('restockItemName').innerText = name;
        document.getElementById('restockUnitLabel').innerText = unit;
        document.getElementById('restockPriceInput').value = price > 0 ? new Intl.NumberFormat('id-ID').format(price) : '';
        document.getElementById('restockPriceRaw').value = price > 0 ? price : '';
        document.getElementById('modal-restock').classList.remove('hidden');
    }
    function closeRestockModal() {
        document.getElementById('modal-restock').classList.add('hidden');
    }

    // Modal Usage
    let currentMaterialStockLimit = 0;
    function openUsageModal(id, name, unit, currentStock) {
        currentMaterialStockLimit = currentStock;
        document.getElementById('formUsage').action = `/warehouse/raw-materials/${id}/usage`;
        document.getElementById('usageItemName').innerText = name;
        document.getElementById('usageUnitLabel').innerText = unit;
        document.getElementById('usageCurrentStockDisplay').innerText = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(currentStock) + ' ' + unit;
        document.getElementById('usageQtyInput').value = '';
        document.getElementById('usageStockWarning').classList.add('hidden');
        document.getElementById('modal-usage').classList.remove('hidden');
    }
    function closeUsageModal() {
        document.getElementById('modal-usage').classList.add('hidden');
    }
    document.getElementById('usageQtyInput')?.addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        const warning = document.getElementById('usageStockWarning');
        const submitBtn = document.getElementById('btnSubmitUsage');
        if (val > currentMaterialStockLimit) {
            warning.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    // Modal Opname
    let opnameSystemStock = 0;
    let opnameCurrentUnit = '';
    function openOpnameModal(id, name, unit, currentStock) {
        opnameSystemStock = currentStock;
        opnameCurrentUnit = unit;
        document.getElementById('formOpname').action = `/warehouse/raw-materials/${id}/opname`;
        document.getElementById('opnameItemName').innerText = name;
        document.getElementById('opnameUnitLabel').innerText = unit;
        document.getElementById('opnameSystemStockDisplay').innerText = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(currentStock) + ' ' + unit;
        document.getElementById('opnameActualInput').value = currentStock;
        calculateOpnameDiff();
        document.getElementById('modal-opname').classList.remove('hidden');
    }
    function closeOpnameModal() {
        document.getElementById('modal-opname').classList.add('hidden');
    }
    function calculateOpnameDiff() {
        const actual = parseFloat(document.getElementById('opnameActualInput').value) || 0;
        const diff = actual - opnameSystemStock;
        const display = document.getElementById('opnameDiffDisplay');
        const formatted = (diff >= 0 ? '+' : '') + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(diff) + ' ' + opnameCurrentUnit;
        display.innerText = formatted;
        if (diff > 0) {
            display.className = 'font-mono font-black text-sm text-emerald-600 block mt-0.5';
        } else if (diff < 0) {
            display.className = 'font-mono font-black text-sm text-rose-600 block mt-0.5';
        } else {
            display.className = 'font-mono font-black text-sm text-stone-600 block mt-0.5';
        }
    }

    // Modal Edit
    let activeEditMaterialId = null;
    function openEditModal(item) {
        activeEditMaterialId = item.id;
        document.getElementById('formEditMaterial').action = `/warehouse/raw-materials/${item.id}`;
        document.getElementById('formDeleteMaterial').action = `/warehouse/raw-materials/${item.id}`;
        document.getElementById('editMaterialCodeDisplay').innerText = item.code;
        document.getElementById('editName').value = item.name;
        document.getElementById('editCategory').value = item.category || '';
        document.getElementById('editUnit').value = item.unit;
        document.getElementById('editMinStock').value = item.min_stock;
        document.getElementById('editNotes').value = item.notes || '';
        const price = parseFloat(item.unit_price) || 0;
        document.getElementById('editPriceDisplay').value = price > 0 ? new Intl.NumberFormat('id-ID').format(price) : '';
        document.getElementById('editPriceRaw').value = price > 0 ? price : '';
        document.getElementById('modal-edit-material').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modal-edit-material').classList.add('hidden');
    }
    function confirmDeleteMaterial() {
        if (confirm('Apakah Anda yakin ingin menghapus bahan baku ini?')) {
            document.getElementById('formDeleteMaterial').submit();
        }
    }
</script>
@endsection
