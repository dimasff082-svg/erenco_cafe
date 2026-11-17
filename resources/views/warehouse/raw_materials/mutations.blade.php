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
                <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Riwayat Mutasi Stok (Buku Kas Stok)</h2>
            </div>
            <p class="text-xs text-[#786e60] mt-0.5 ml-7">Log audit trail mutasi keluar-masuk, pemakaian bar, dan penyesuaian opname seluruh bahan baku.</p>
        </div>
        <div>
            <a href="{{ route('warehouse.raw_materials.index') }}"
                class="inline-flex items-center gap-2 bg-[#262017] hover:bg-stone-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Katalog Bahan Baku</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs">
        <form action="{{ route('warehouse.raw_materials.mutations') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Pilihan Bahan Baku -->
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Filter Bahan</label>
                <select name="raw_material_id" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Bahan Baku</option>
                    @foreach($rawMaterials as $mat)
                        <option value="{{ $mat->id }}" {{ request('raw_material_id') == $mat->id ? 'selected' : '' }}>
                            {{ $mat->code }} - {{ $mat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jenis Mutasi -->
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Jenis Mutasi</label>
                <select name="type" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Jenis Mutasi</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk / Restock (+)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar / Pemakaian (-)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian / Opname (&plusmn;)</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2.5 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <!-- Sampai Tanggal -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-[#8f8573] uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2.5 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <!-- Tombol Aksi -->
            <div class="sm:col-span-1 flex items-end justify-end gap-1 pt-4 sm:pt-0">
                <button type="submit" class="w-full bg-[#c59b27] hover:bg-[#a98119] text-stone-950 font-bold py-2 px-3 rounded-xl text-xs transition">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Buku Mutasi Stok -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#e5dfd3] bg-[#faf7f0] text-[#5c5346] font-serif-classic font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Bahan Baku</th>
                        <th class="py-3 px-4 text-center">Jenis Mutasi</th>
                        <th class="py-3 px-4 text-right">Jumlah Mutasi</th>
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
                            $unit = $mut->rawMaterial?->unit ?? '';
                        @endphp
                        <tr class="hover:bg-[#faf7f0]/60 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-[#262017] block">{{ $mut->created_at->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-[#8f8573] font-mono">{{ $mut->created_at->format('H:i') }} WITA</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($mut->rawMaterial)
                                    <a href="{{ route('warehouse.raw_materials.history', $mut->raw_material_id) }}" class="font-bold text-[#262017] hover:text-[#c59b27] transition block">
                                        {{ $mut->rawMaterial->name }}
                                    </a>
                                    <span class="font-mono text-[10px] text-[#8f8573]">{{ $mut->rawMaterial->code }}</span>
                                @else
                                    <span class="text-stone-400 italic">Bahan Dihapus</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-xs {{ $mut->type === 'in' ? 'text-emerald-700' : ($mut->type === 'out' ? 'text-rose-700' : 'text-amber-800') }}">
                                {{ $badge['sign'] }}{{ number_format(abs($mut->quantity), 2, ',', '.') }} <span class="text-[10px] font-normal text-stone-500">{{ $unit }}</span>
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
                            <td colspan="9" class="py-12 text-center text-[#8f8573]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm font-bold text-[#262017]">Belum Ada Riwayat Mutasi Stok</p>
                                <p class="text-xs text-[#8f8573] mt-0.5">Semua pergerakan keluar-masuk bahan baku akan otomatis tercatat di sini.</p>
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
