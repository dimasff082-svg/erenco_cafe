@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Detail Transaksi Penjualan</h2>
            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $sale->transaction_number }} &bull; {{ $sale->transaction_date->format('d F Y, H:i:s') }} WIB</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->check() && (auth()->user()->isOwner() || auth()->user()->isAdmin()) && ! $sale->isCancelled())
                <button type="button" onclick="document.getElementById('modal-cancel-show').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Batalkan Pesanan
                </button>
            @endif
            <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Buka / Cetak Struk Digital
            </a>
            <a href="{{ request()->routeIs('owner.*') ? route('owner.transactions.index') : route('admin.transactions.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2.5 rounded-xl transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    @if($sale->isCancelled())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-rose-900">Pesanan Telah Dibatalkan oleh {{ $sale->canceller ? ($sale->canceller->isOwner() ? 'Pemilik Toko' : 'Administrator') : 'Pengelola' }}</h4>
                    <p class="text-rose-700">
                        Dibatalkan oleh: <strong>{{ $sale->canceller?->name ?? 'Pengelola' }}</strong>
                        @if($sale->cancelled_at)
                            &bull; Tanggal: {{ $sale->cancelled_at->format('d/m/Y H:i:s') }} WIB
                        @endif
                    </p>
                    <p class="text-rose-800 bg-white/70 p-2.5 rounded-xl border border-rose-200 mt-1">
                        <strong>Alasan Pembatalan:</strong> "{{ $sale->cancellation_reason }}"
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Metadata Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Kasir Bertugas</span>
            <span class="text-base font-bold text-slate-900">{{ $sale->user->name ?? '-' }}</span>
            <span class="text-xs text-slate-400 block mt-0.5">{{ $sale->user->email ?? '' }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Metode & Status</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm font-bold text-slate-900">{{ $sale->payment->methodLabel() ?? 'Tunai' }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Lunas</span>
            </div>
            <span class="text-xs text-slate-500 block mt-1">Bayar: Rp {{ number_format($sale->payment->amount_tendered, 0, ',', '.') }} | Kembali: Rp {{ number_format($sale->payment->change_amount, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Tagihan</span>
            <span class="text-2xl font-black text-emerald-600">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            <span class="text-xs text-slate-400 block mt-0.5">{{ $sale->items->sum('quantity') }} total barang terjual</span>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-base text-slate-800">Rincian Barang Terjual (Data Snapshot)</h3>
            <p class="text-xs text-slate-400">Harga dan nama saat transaksi berlangsung tetap terlindungi meskipun data barang di master berubah.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6">SKU Snapshot</th>
                        <th class="py-3.5 px-6">Nama Barang Snapshot</th>
                        <th class="py-3.5 px-6 text-center">Jumlah</th>
                        <th class="py-3.5 px-6 text-right">Harga Satuan Saat Itu</th>
                        <th class="py-3.5 px-6 text-right">Subtotal Baris</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($sale->items as $it)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6 font-mono text-xs font-bold text-slate-600">
                                {{ $it->sku_snapshot }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900">
                                {{ $it->product_name_snapshot }}
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-slate-800">
                                {{ $it->quantity }}
                            </td>
                            <td class="py-4 px-6 text-right text-xs text-slate-600">
                                Rp {{ number_format($it->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-black text-slate-900 text-sm">
                                Rp {{ number_format($it->line_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-black text-slate-900">
                        <td colspan="4" class="py-4 px-6 text-right text-xs uppercase tracking-wider">Total Transaksi:</td>
                        <td class="py-4 px-6 text-right text-base text-emerald-700">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if(auth()->check() && (auth()->user()->isOwner() || auth()->user()->isAdmin()) && ! $sale->isCancelled())
    <div id="modal-cancel-show" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-900/60 backdrop-blur-2xs flex items-center justify-center p-4" onclick="document.getElementById('modal-cancel-show').classList.add('hidden')">
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-md border border-rose-200" onclick="event.stopPropagation()">
            <form method="POST" action="{{ request()->routeIs('owner.*') ? route('owner.transactions.cancel', $sale->id) : route('admin.transactions.cancel', $sale->id) }}">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 text-rose-600 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center font-bold text-lg">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-serif-classic text-lg font-bold text-[#262017]">Batalkan Transaksi</h3>
                            <p class="text-xs text-[#786e60]">{{ $sale->transaction_number }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-[#5c5346] leading-relaxed mb-4">
                        Konfirmasi pembatalan pesanan oleh <strong>{{ auth()->user()->isOwner() ? 'Pemilik Toko' : 'Administrator' }}</strong>. Transaksi ini akan ditandai batal dan tidak dihitung ke pendapatan.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-[#262017] mb-1">
                            Alasan Pembatalan <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="cancellation_reason" rows="3" required
                            placeholder="Contoh: Kasir salah input menu / Pelanggan batalkan pesanan..."
                            class="w-full text-xs p-3 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-rose-500"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-[#faf8f5] border-t border-[#f0ece1] flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-cancel-show').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-white">
                        Tutup
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                        Ya, Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
