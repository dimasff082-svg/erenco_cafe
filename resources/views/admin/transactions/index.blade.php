@extends('layouts.app')

@section('content')
@php
    $currentRoute = request()->routeIs('owner.*') ? route('owner.transactions.index') : route('admin.transactions.index');
    $isTodayActive = request('period') === 'today' || (request('date_from') === now()->toDateString() && request('date_to') === now()->toDateString());
@endphp

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Riwayat Seluruh Transaksi</h2>
            <p class="text-xs text-[#786e60] mt-0.5 font-sans">Daftar rekaman seluruh transaksi penjualan menu di resto & cafe. Klik baris transaksi untuk melihat rincian menu secara langsung.</p>
        </div>

        <!-- Quick Period Filter Pills -->
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['period' => 'today', 'date_from' => null, 'date_to' => null]) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ $isTodayActive ? 'bg-[#c59b27] text-stone-950 font-black ring-2 ring-[#a98119]/50' : 'bg-white hover:bg-[#f6f2e9] text-[#362f25] border border-[#dcd4c5]' }}">
                <svg class="w-3.5 h-3.5 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Hari Ini
            </a>
            @if($isTodayActive || request()->hasAny(['search', 'user_id', 'payment_method', 'date_from', 'date_to']))
                <a href="{{ $currentRoute }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#f0ebd9] hover:bg-[#e4ddc8] text-[#5c5346] border border-[#dcd4c5] transition">
                    Semua Transaksi
                </a>
            @endif
        </div>
    </div>

    <!-- Summary Cards (Hasil Keseluruhan Transaksi & Uang Masuk Toko) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Total Seluruh Transaksi -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Keseluruhan Transaksi</span>
                <span class="text-2xl font-black text-[#26211a] mt-1 block">
                    {{ number_format($summary['total_transactions'] ?? 0, 0, ',', '.') }} <span class="text-xs font-semibold text-[#8f8573]">Transaksi</span>
                </span>
                <p class="text-[11px] text-[#8f8573] mt-0.5">Total akumulasi seluruh transaksi pada sistem</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-center text-xl text-[#c59b27] shadow-2xs">
                📊
            </div>
        </div>

        <!-- Total Seluruh Uang Masuk (Omzet) -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Total Uang Masuk (Omzet)</span>
                <span class="text-2xl font-black text-[#15803d] mt-1 block">
                    Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-[#8f8573] mt-0.5">Akumulasi uang masuk lunas dari seluruh transaksi</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-xl text-emerald-600 shadow-2xs">
                💵
            </div>
        </div>
    </div>

    <!-- Filters Form -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] p-4">
        <form action="{{ $currentRoute }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            @if($isTodayActive && !request('date_from'))
                <input type="hidden" name="period" value="today">
            @endif

            <div class="sm:col-span-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari No. TRX / Meja / Nama..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="user_id" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="">Semua Kasir / Petugas</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="payment_method" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="midtrans" {{ request('payment_method') === 'midtrans' ? 'selected' : '' }}>Midtrans (Online)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1.5">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-1/2 bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2 py-2 text-[11px] text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-1/2 bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2 py-2 text-[11px] text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] text-xs font-semibold px-4 py-2 rounded-xl transition shadow-xs">
                    Saring
                </button>
                @if(request()->hasAny(['search', 'user_id', 'payment_method', 'date_from', 'date_to', 'period']))
                    <a href="{{ $currentRoute }}" class="bg-[#f0ebd9] hover:bg-[#e4ddc8] text-[#5c5346] text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table with Accordion Rows -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#faf7f0] border-b border-[#e5dfd3] text-[#786e60] text-xs font-bold uppercase tracking-wider font-serif-classic">
                        <th class="py-3.5 px-4 w-10 text-center"></th>
                        <th class="py-3.5 px-6">No. Transaksi</th>
                        <th class="py-3.5 px-6">Waktu</th>
                        <th class="py-3.5 px-6">Meja & Pelanggan</th>
                        <th class="py-3.5 px-6">Kasir Bertugas</th>
                        <th class="py-3.5 px-6 text-center">Metode Bayar</th>
                        <th class="py-3.5 px-6 text-center">Jumlah Menu</th>
                        <th class="py-3.5 px-6 text-right">Total Transaksi</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9] font-medium">
                    @forelse($sales as $sale)
                        <!-- Main Transaction Row (Clickable to Expand) -->
                        <tr onclick="toggleTrxDetail('{{ $sale->accordion_id }}')"
                            class="hover:bg-[#faf7f0]/80 transition cursor-pointer select-none group">
                            <td class="py-4 px-4 text-center text-slate-400 group-hover:text-[#c59b27] transition">
                                <svg id="chevron-{{ $sale->accordion_id }}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs font-bold text-[#8a681c]">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $sale->transaction_number }}</span>
                                    @if($sale->is_open_bill)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Open Bill ({{ $sale->orders_count }} Pesanan)
                                        </span>
                                    @elseif($sale->is_takeaway || $sale->badge_label === 'Takeaway' || $sale->table_number === 'TAKEAWAY' || str_contains($sale->table_number ?? '', 'Takeaway'))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-300 shadow-2xs">
                                            🛍️ Takeaway
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#f0ebd9] text-[#786e60]">
                                            Reguler
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-xs text-[#786e60]">
                                {{ $sale->transaction_date->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 px-6 text-xs text-[#262017]">
                                <span class="font-bold block">
                                    @if($sale->is_takeaway || $sale->badge_label === 'Takeaway' || $sale->table_number === 'TAKEAWAY' || str_contains($sale->table_number ?? '', 'Takeaway'))
                                        🛍️ Bawa Pulang
                                    @elseif($sale->table_number)
                                        {{ str_starts_with($sale->table_number, 'Meja') ? $sale->table_number : 'Meja ' . $sale->table_number }}
                                    @else
                                        Non-Meja
                                    @endif
                                </span>
                                <span class="text-[11px] text-[#8c8273]">{{ $sale->customer_name ?: 'Pelanggan Walk-in' }}</span>
                            </td>
                            <td class="py-4 px-6 text-xs font-bold text-[#262017]">
                                {{ $sale->cashier_name ?? ($sale->confirmer->name ?? ($sale->user->name ?? 'Kasir')) }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-[#faf7f0] border border-[#e5dfd3] text-[#5c5346]">
                                    {{ $sale->payment?->methodLabel() ?? 'Tunai' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center text-xs font-bold text-[#5c5346]">
                                {{ $sale->items->sum('quantity') }} porsi
                            </td>
                            <td class="py-4 px-6 text-right text-xs font-black font-serif-classic text-[#1b4332]">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center" onclick="event.stopPropagation()">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <button type="button" onclick="toggleTrxDetail('{{ $sale->accordion_id }}')"
                                        title="Lihat Detail Transaksi"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-[#8a681c] hover:bg-[#c59b27] hover:text-stone-950 border border-amber-300/80 transition shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <a href="{{ $sale->receipt_url }}" target="_blank"
                                        title="Cetak Struk Digital"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-800 hover:bg-[#1b4332] hover:text-emerald-100 border border-emerald-300 transition shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Accordion Detail Row: Displays Directly Below the Clicked Transaction -->
                        <tr id="trx-detail-{{ $sale->accordion_id }}" class="hidden bg-[#faf7f0]/60 border-b-2 border-[#dfb15b]/40">
                            <td colspan="9" class="p-4 sm:p-6">
                                <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-sm p-5 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#f0ebd9]">
                                        <div class="flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 border border-amber-200/80 flex items-center justify-center font-bold text-xs">
                                                {{ $sale->table_number ?: 'TB' }}
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-serif-classic font-bold text-sm text-[#262017]">
                                                        Rincian Transaksi: {{ $sale->transaction_number }}
                                                    </h4>
                                                    @if($sale->is_open_bill)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">
                                                            Open Bill ({{ $sale->orders_count }} Pesanan)
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-[#736858]">
                                                    {{ $sale->table_number ? 'Meja ' . $sale->table_number : 'Non-Meja' }} &bull; Pelanggan: <strong class="text-[#262017]">{{ $sale->customer_name ?: 'Umum' }}</strong>
                                                    &bull; Waktu: {{ $sale->transaction_date->format('d M Y, H:i') }} WIB
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-[11px] text-slate-500">Dilayani / Dikonfirmasi Oleh:</span>
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-bold text-slate-800">
                                                {{ $sale->cashier_name ?? ($sale->confirmer->name ?? ($sale->user->name ?? 'Kasir')) }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($sale->is_open_bill)
                                        <!-- Open Bill Highlight Banner -->
                                        <div class="p-3.5 rounded-xl bg-amber-50/90 border border-amber-200 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                            <div class="flex items-center gap-2.5">
                                                <span class="px-2.5 py-1 rounded-lg bg-amber-200/80 text-amber-900 font-extrabold text-xs uppercase tracking-wider">
                                                    Riwayat Open Bill
                                                </span>
                                                <span class="text-xs">
                                                    Sesi Meja <strong>{{ $sale->table_number }}</strong> menyatukan <strong>{{ $sale->orders_count }} pesanan</strong> ke dalam satu riwayat transaksi ini.
                                                </span>
                                            </div>
                                            <span class="text-xs font-mono font-bold text-amber-800">
                                                No. Bill: {{ $sale->transaction_number }}
                                            </span>
                                        </div>

                                        <!-- Sub-Orders Breakdown -->
                                        <div class="space-y-2">
                                            <div class="text-[11px] font-bold text-[#786e60] uppercase tracking-wider">
                                                Daftar Pesanan Tergabung ({{ $sale->orders_count }} Pesanan):
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                @foreach($sale->orders as $order)
                                                    <div class="p-2.5 rounded-xl bg-[#faf7f0] border border-[#e5dfd3] flex justify-between items-center text-xs">
                                                        <div>
                                                            <div class="font-mono font-bold text-[#8a681c]">{{ $order->transaction_number }}</div>
                                                            <div class="text-[10px] text-[#8c8273]">
                                                                {{ $order->transaction_date->format('H:i') }} WIB &bull; {{ $order->items->sum('quantity') }} porsi
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <div class="font-bold font-serif-classic text-[#1b4332]">
                                                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                                            </div>
                                                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                                                {{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Items Ordered Table -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs text-left">
                                            <thead>
                                                <tr class="text-[#8c806e] uppercase tracking-wider font-bold border-b border-[#f2ede2]">
                                                    <th class="pb-2">Menu Dipesan</th>
                                                    <th class="pb-2 text-center">Jumlah</th>
                                                    <th class="pb-2 text-right">Harga Satuan</th>
                                                    <th class="pb-2 text-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#f7f4ed]">
                                                @foreach($sale->items as $item)
                                                    <tr>
                                                        <td class="py-2.5 font-bold text-[#262017]">
                                                            {{ $item->product_name_snapshot }}
                                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $item->sku_snapshot }}</span>
                                                        </td>
                                                        <td class="py-2.5 text-center font-bold text-slate-700">
                                                            {{ $item->quantity }}
                                                        </td>
                                                        <td class="py-2.5 text-right text-slate-600">
                                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                        </td>
                                                        <td class="py-2.5 text-right font-black text-slate-900">
                                                            Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Bottom Payment Summary Box -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-[#f0ebd9] items-center">
                                        <div class="text-xs space-y-1 text-[#786e60]">
                                            <div>
                                                Metode Pembayaran: <strong class="text-[#262017] uppercase">{{ $sale->payment?->methodLabel() ?? 'Tunai' }}</strong>
                                            </div>
                                            @if($sale->payment && $sale->payment->method === 'cash')
                                                <div>
                                                    Uang Diterima: <strong class="text-[#262017]">Rp {{ number_format($sale->payment->amount_tendered, 0, ',', '.') }}</strong>
                                                    &bull; Kembalian: <strong class="text-emerald-700">Rp {{ number_format($sale->payment->change_amount, 0, ',', '.') }}</strong>
                                                </div>
                                            @elseif($sale->payment && $sale->payment->reference_number)
                                                <div>
                                                    No. Referensi: <span class="font-mono text-[11px] text-slate-700">{{ $sale->payment->reference_number }}</span>
                                                </div>
                                            @endif
                                            @if($sale->notes)
                                                <div class="text-[11px] italic text-slate-500">
                                                    Catatan: "{{ $sale->notes }}"
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-right flex flex-col items-end gap-2">
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-bold text-[#8c806e] uppercase">Total Transaksi:</span>
                                                <span class="text-xl font-serif-classic font-black text-[#1b4332]">
                                                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ $sale->receipt_url }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    Cetak Struk
                                                </a>
                                                <a href="{{ request()->routeIs('owner.*') ? route('owner.transactions.show', $sale->id) : route('admin.transactions.show', $sale->id) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                                    Halaman Detail &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-[#998f80] text-sm">
                                <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-center text-[#b8a994]">
                                    <svg class="w-6 h-6 text-[#b8a994]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="font-serif-classic font-bold text-[#262017]">Tidak ada transaksi ditemukan</p>
                                <p class="text-xs text-[#786e60] mt-0.5">Coba sesuaikan filter pencarian atau tanggal transaksi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="p-4 border-t border-[#f0ebd9] bg-[#faf7f0]">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleTrxDetail(id) {
        const detailRow = document.getElementById('trx-detail-' + id);
        const chevron = document.getElementById('chevron-' + id);
        if (!detailRow) return;

        if (detailRow.classList.contains('hidden')) {
            detailRow.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            detailRow.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }
</script>
@endsection
