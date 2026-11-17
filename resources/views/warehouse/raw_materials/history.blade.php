@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('warehouse.raw_materials.index') }}" class="p-1 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">{{ $rawMaterial->name }}</h2>
                <span class="font-mono text-xs font-bold text-[#c59b27] bg-[#faf7f0] border border-[#dcd4c5] px-2 py-0.5 rounded-md">
                    {{ $rawMaterial->code }}
                </span>
            </div>
            <p class="text-xs text-[#786e60] mt-0.5 ml-7">
                Kartu Stok Fisik &bull; Kategori: <strong class="text-stone-700">{{ $rawMaterial->category }}</strong> &bull; Satuan: <strong class="text-stone-700">{{ $rawMaterial->unit }}</strong> &bull; Harga Beli: <strong class="text-stone-700">Rp {{ number_format($rawMaterial->unit_price, 0, ',', '.') }}/{{ $rawMaterial->unit }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.raw_materials.index') }}"
                class="inline-flex items-center gap-2 bg-[#fcfaf5] hover:bg-[#ede7da] text-[#5c5346] text-xs font-bold px-3.5 py-2.5 rounded-xl border border-[#dcd4c5] shadow-2xs transition">
                <svg class="w-4 h-4 text-[#8f8573]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Katalog</span>
            </a>
        </div>
    </div>

    <!-- 3 Kartu Ringkasan Akumulasi Item -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Masuk -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Akumulasi Masuk / Restock</span>
                <span class="text-2xl font-black text-emerald-700 mt-0.5 block">
                    +{{ number_format($itemStats['total_in'], 2, ',', '.') }} <span class="text-xs font-semibold text-stone-500">{{ $rawMaterial->unit }}</span>
                </span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">Seluruh restock tercatat</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
        </div>

        <!-- Total Keluar -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">Akumulasi Keluar / Pemakaian</span>
                <span class="text-2xl font-black text-rose-700 mt-0.5 block">
                    -{{ number_format($itemStats['total_out'], 2, ',', '.') }} <span class="text-xs font-semibold text-stone-500">{{ $rawMaterial->unit }}</span>
                </span>
                <p class="text-[10px] text-[#8f8573] mt-0.5">Pemakaian bar, dapur & waste</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
            </div>
        </div>

        <!-- Saldo Stok Saat Ini -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Saldo Stok Fisik Saat Ini</span>
                <span class="text-2xl font-black text-[#262017] mt-0.5 block">
                    {{ number_format($rawMaterial->current_stock, 2, ',', '.') }} <span class="text-xs font-semibold text-stone-500">{{ $rawMaterial->unit }}</span>
                </span>
                <p class="text-[10px] text-amber-800 font-bold mt-0.5">Estimasi Aset: Rp {{ number_format($itemStats['total_value'], 0, ',', '.') }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#c59b27] shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs">
        <form action="{{ route('warehouse.raw_materials.history', $rawMaterial->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Jenis Mutasi -->
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Jenis Mutasi</label>
                <select name="type" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Jenis Mutasi</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk / Restock (+)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar / Pemakaian (-)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian / Opname (&plusmn;)</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2.5 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <!-- Sampai Tanggal -->
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2.5 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <!-- Tombol Filter -->
            <div class="sm:col-span-2 flex items-end justify-end gap-1 pt-4 sm:pt-0">
                <button type="submit" class="w-full bg-[#c59b27] hover:bg-[#a98119] text-stone-950 font-bold py-2 px-3 rounded-xl text-xs transition">
                    Filter Kartu
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Kartu Riwayat Mutasi -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#e5dfd3] bg-[#faf7f0] text-[#5c5346] font-serif-classic font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4 text-center">Jenis Mutasi</th>
                        <th class="py-3 px-4 text-right">Perubahan Qty</th>
                        <th class="py-3 px-4 text-right">Saldo Sebelum</th>
                        <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                        <th class="py-3 px-4">No. Referensi</th>
                        <th class="py-3 px-4">Keterangan / Alasan</th>
                        <th class="py-3 px-4 text-center">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9]">
                    @forelse($mutations as $mut)
                        @php
                            $badge = $mut->typeBadge();
                        @endphp
                        <tr class="hover:bg-[#faf7f0]/60 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-[#262017] block">{{ $mut->created_at->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-[#8f8573] font-mono">{{ $mut->created_at->format('H:i') }} WITA</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-xs {{ $mut->type === 'in' ? 'text-emerald-700' : ($mut->type === 'out' ? 'text-rose-700' : 'text-amber-800') }}">
                                {{ $badge['sign'] }}{{ number_format(abs($mut->quantity), 2, ',', '.') }} <span class="text-[10px] font-normal text-stone-500">{{ $rawMaterial->unit }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-stone-600">
                                {{ number_format($mut->stock_before, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#262017]">
                                {{ number_format($mut->stock_after, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-stone-700">
                                {{ $mut->reference_number ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-[11px] text-[#5c5346] max-w-xs">
                                {{ $mut->notes ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-center text-[11px] text-[#5c5346] whitespace-nowrap">
                                {{ $mut->user?->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-[#8f8573]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm font-bold text-[#262017]">Belum Ada Mutasi Untuk Bahan Ini</p>
                                <p class="text-xs text-[#8f8573] mt-0.5">Catatan keluar-masuk item ini akan tampil secara kronologis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutations->hasPages())
            <div class="p-4 border-t border-[#e5dfd3] bg-[#faf7f0]">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
