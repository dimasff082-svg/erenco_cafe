@extends('layouts.app', ['title' => 'Dashboard Pemilik - Analisis Finansial'])

@section('content')
<div class="space-y-8">
    <!-- Header Page Info & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-[#e5dfd3]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-bold tracking-widest uppercase px-2.5 py-0.5 rounded-full bg-[#f4ece0] text-[#8a681c] border border-[#dfb15b]/30">Eksekutif Insight</span>
                <span class="text-xs text-[#8c8273] font-serif-classic">Ikhtisar Kinerja Finansial</span>
            </div>
            <h2 class="text-2xl font-serif-classic font-bold text-[#262017] tracking-tight">Dashboard Pemilik Toko</h2>
            <p class="text-xs text-[#736858] mt-0.5">Pantau real-time metrik inti: Omzet, Total Transaksi, Menu Terjual, dan Rata-rata Nilai Transaksi resto & cafe Anda.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Period Filter Pills -->
            <div class="bg-white border border-[#dcd4c5] p-1 rounded-xl shadow-xs flex items-center text-xs font-semibold">
                <a href="{{ route('owner.dashboard', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-lg transition {{ $period === 'today' ? 'bg-[#c59b27] text-stone-950 font-bold shadow-xs' : 'text-[#6b6253] hover:text-[#2c261e]' }}">
                    Hari Ini
                </a>
                <a href="{{ route('owner.dashboard', ['period' => 'month']) }}"
                    class="px-3 py-1.5 rounded-lg transition {{ $period === 'month' ? 'bg-[#c59b27] text-stone-950 font-bold shadow-xs' : 'text-[#6b6253] hover:text-[#2c261e]' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('owner.dashboard', ['period' => 'year']) }}"
                    class="px-3 py-1.5 rounded-lg transition {{ $period === 'year' ? 'bg-[#c59b27] text-stone-950 font-bold shadow-xs' : 'text-[#6b6253] hover:text-[#2c261e]' }}">
                    Tahun Ini
                </a>
                <a href="{{ route('owner.dashboard', ['period' => 'all']) }}"
                    class="px-3 py-1.5 rounded-lg transition {{ $period === 'all' ? 'bg-[#c59b27] text-stone-950 font-bold shadow-xs' : 'text-[#6b6253] hover:text-[#2c261e]' }}">
                    Semua Waktu
                </a>
            </div>

            <a href="{{ route('owner.reports.index') }}"
                class="inline-flex items-center gap-2 bg-[#ffffff] hover:bg-[#f6f2e9] border border-[#dcd4c5] text-[#362f25] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 text-[#8a681c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Buka Laporan
            </a>
        </div>
    </div>

    <!-- 4 Core Executive KPI Cards (Omzet, Total Struk, Menu Terjual, Rata-rata Transaksi) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. OMZET -->
        <div class="bg-white rounded-2xl p-6 border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] relative overflow-hidden group hover:border-[#c59b27] transition">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-[#c59b27]/10 to-transparent rounded-bl-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#8a7f70] uppercase tracking-wider">1. Omzet Penjualan</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-[#8a681c] border border-amber-200/80 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-serif-classic font-bold text-[#262017] tracking-tight">
                Rp {{ number_format($omzet, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-[#786e60] mt-2 flex items-center justify-between pt-2 border-t border-[#f0ebd9]">
                <span>Total Struk: <strong class="text-[#262017]">{{ $totalStruk }}</strong></span>
                <span class="text-[10px] text-[#8a7f70]">All-time: Rp {{ number_format($omzetAllTime, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- 2. TOTAL STRUK SELESAI -->
        <div class="bg-white rounded-2xl p-6 border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] relative overflow-hidden group hover:border-[#1b4332] transition">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-600/10 to-transparent rounded-bl-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#8a7f70] uppercase tracking-wider">2. Transaksi Selesai</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#1b4332] border border-emerald-200/80 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-serif-classic font-bold text-[#1b4332] tracking-tight">
                {{ number_format($totalStruk) }} <span class="text-sm font-normal text-[#8a7f70]">struk</span>
            </div>
            <div class="text-[11px] text-[#786e60] mt-2 flex items-center justify-between pt-2 border-t border-[#f0ebd9]">
                <span>Pesanan Berhasil</span>
                <span class="text-[10px] text-[#8a7f70]">All-time: {{ number_format($totalStrukAllTime) }} struk</span>
            </div>
        </div>

        <!-- 3. MENU TERJUAL -->
        <div class="bg-white rounded-2xl p-6 border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] relative overflow-hidden group hover:border-[#8a681c] transition">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-amber-500/10 to-transparent rounded-bl-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#8a7f70] uppercase tracking-wider">3. Menu Terjual</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-[#8a681c] border border-amber-200/80 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-serif-classic font-bold text-[#262017] tracking-tight">
                {{ number_format($totalItemsSold) }} <span class="text-sm font-normal text-[#8a7f70]">porsi/item</span>
            </div>
            <div class="text-[11px] text-[#786e60] mt-2 flex items-center justify-between pt-2 border-t border-[#f0ebd9]">
                <span>Volume menu disajikan</span>
                <span class="text-[10px] text-[#8a7f70]">Periode terpilih</span>
            </div>
        </div>

        <!-- 4. RATA-RATA TRANSAKSI (AOV) -->
        <div class="bg-white rounded-2xl p-6 border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] relative overflow-hidden group hover:border-[#3d5a80] transition">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-sky-600/10 to-transparent rounded-bl-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#8a7f70] uppercase tracking-wider">4. Rata-rata Transaksi</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#1d3557] border border-sky-200/80 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-serif-classic font-bold text-[#1d3557] tracking-tight">
                Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-[#786e60] mt-2 flex items-center justify-between pt-2 border-t border-[#f0ebd9]">
                <span>Nilai rata-rata / struk</span>
                <span class="text-[10px] text-[#8a7f70]">{{ number_format($totalStruk) }} Transaksi</span>
            </div>
        </div>
    </div>

    <!-- Secondary Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Selling Menu in Period -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-[#e5dfd3] shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#f0ebd9] pb-3">
                <div>
                    <h3 class="font-serif-classic font-bold text-base text-[#262017]">Menu Kontributor Penjualan Terbesar</h3>
                    <p class="text-xs text-[#736858]">Menu makanan dan minuman terlaris pada periode ini.</p>
                </div>
                <span class="text-xs font-semibold text-[#8a681c] bg-[#fbf6ec] border border-[#dfb15b]/30 px-3 py-1 rounded-xl">Top 5</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#f0ebd9] text-[#8a7f70] font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Menu</th>
                            <th class="py-2.5 px-3 text-center">Porsi Terjual</th>
                            <th class="py-2.5 px-3 text-right">Total Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7f4ed]">
                        @forelse($topProducts as $item)
                            <tr class="hover:bg-[#faf7f0] transition">
                                <td class="py-3 px-3">
                                    <p class="font-bold text-[#262017]">{{ $item->product_name_snapshot }}</p>
                                    <span class="font-mono text-[10px] text-[#8c8273]">{{ $item->sku_snapshot }}</span>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-[#262017]">
                                    {{ number_format($item->total_qty) }}
                                </td>
                                <td class="py-3 px-3 text-right font-medium text-[#262017]">
                                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-[#998f80] text-xs">
                                    Belum ada transaksi penjualan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#f0ebd9] pb-3">
                <div>
                    <h3 class="font-serif-classic font-bold text-base text-[#262017]">Transaksi Terkini</h3>
                    <p class="text-xs text-[#736858]">Aktivitas kasir terbaru.</p>
                </div>
                <a href="{{ route('owner.reports.index') }}" class="text-xs font-semibold text-[#8a681c] hover:underline">
                    Semua &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentSales as $sale)
                    <div class="p-3 rounded-xl bg-[#faf7f0] border border-[#eee7d8] flex items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-mono text-[10px] font-bold text-[#8a681c]">{{ $sale->transaction_number }}</span>
                                @if($sale->isCancelled())
                                    <span class="inline-flex px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800">
                                        Dibatalkan
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-[#52493d] mt-0.5">
                                Kasir: <strong class="text-[#262017]">{{ $sale->user->name ?? 'Kasir' }}</strong>
                            </p>
                            <span class="text-[10px] text-[#918778]">{{ $sale->transaction_date->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-right flex flex-col items-end gap-1">
                            <span class="text-xs font-serif-classic font-bold text-[#262017]">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </span>
                            <div class="flex items-center gap-1">
                                <span class="inline-block text-[9px] uppercase font-bold px-2 py-0.5 rounded-md bg-stone-200 text-stone-700">
                                    {{ strtoupper($sale->payment->method ?? 'TUNAI') }}
                                </span>
                                @if(! $sale->isCancelled())
                                    <button type="button"
                                        onclick="openCancelOrderModal('{{ $sale->id }}', '{{ $sale->transaction_number }}')"
                                        class="px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-300 text-[10px] font-bold transition">
                                        Batal
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-[#998f80] text-xs">
                        Belum ada transaksi yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembatalan Pesanan oleh Pemilik Toko -->
<div id="modal-cancel-order" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-900/60 backdrop-blur-2xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-md border border-[#e5dfd3]" onclick="event.stopPropagation()">
        <form id="form-cancel-order" method="POST" action="">
            @csrf
            <div class="p-6">
                <div class="flex items-center gap-3 pb-3 border-b border-[#f0ece1]">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Wewenang Pemilik Toko</span>
                        <h3 class="font-serif-classic text-lg font-bold text-[#262017]" id="cancel-trx-title">Batalkan Transaksi Pesanan</h3>
                    </div>
                </div>

                <p class="text-xs text-[#5c5346] leading-relaxed my-3">
                    Pesanan yang dibatalkan oleh <strong>Pemilik Toko</strong> akan ditandai status batal, dibatalkan dari antrean dapur, dan dikeluarkan dari perhitungan omzet.
                </p>

                <div>
                    <label class="block text-xs font-bold text-[#262017] mb-1">
                        Alasan Pembatalan <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="cancellation_reason" rows="3" required
                        placeholder="Contoh: Kesalahan input menu / Pembatalan oleh pembeli..."
                        class="w-full text-xs p-3 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-rose-500"></textarea>
                </div>
            </div>

            <div class="p-4 bg-[#faf8f5] border-t border-[#f0ece1] flex items-center justify-end gap-2">
                <button type="button" onclick="closeCancelOrderModal()" class="px-4 py-2 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-white">
                    Tutup
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                    Ya, Batalkan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCancelOrderModal(saleId, trxNumber) {
        const modal = document.getElementById('modal-cancel-order');
        const form = document.getElementById('form-cancel-order');
        const title = document.getElementById('cancel-trx-title');
        if (!modal || !form) return;

        form.action = '/owner/transactions/' + saleId + '/cancel';
        if (title) {
            title.innerText = 'Batalkan ' + (trxNumber || 'Pesanan');
        }
        modal.classList.remove('hidden');
    }

    function closeCancelOrderModal() {
        const modal = document.getElementById('modal-cancel-order');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
