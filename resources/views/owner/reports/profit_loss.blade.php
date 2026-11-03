@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md bg-[#c59b27] text-stone-950 text-[10px] font-black uppercase tracking-wider">
                    ROLE PEMILIK TOKO
                </span>
                <span class="text-xs text-[#786e60] font-medium">Periode: {{ $report['period_label'] }}</span>
            </div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight mt-1">Laporan Laba / Rugi</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Analisis pendapatan penjualan dikurangi beban bahan baku, operasional toko, dan penggajian karyawan.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 bg-[#262017] hover:bg-black text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
        <form method="GET" action="{{ route('owner.profit_loss.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('owner.profit_loss.index', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('period') === 'today' ? 'bg-[#c59b27] text-stone-950 font-bold' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    Hari Ini
                </a>
                <a href="{{ route('owner.profit_loss.index', ['period' => 'this_month']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('period', 'this_month') === 'this_month' && !request('date_from') ? 'bg-[#c59b27] text-stone-950 font-bold' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('owner.profit_loss.index', ['period' => 'last_month']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('period') === 'last_month' ? 'bg-[#c59b27] text-stone-950 font-bold' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    Bulan Lalu
                </a>
                <a href="{{ route('owner.profit_loss.index', ['period' => 'this_year']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('period') === 'this_year' ? 'bg-[#c59b27] text-stone-950 font-bold' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    Tahun Ini
                </a>
            </div>

            <div class="h-4 w-px bg-stone-300 hidden sm:block"></div>

            <!-- Custom Date Range -->
            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="text-xs px-3 py-1.5 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                <span class="text-xs text-[#786e60]">s/d</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="text-xs px-3 py-1.5 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#262017] text-white text-xs font-semibold hover:bg-black transition">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- 4 Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Bersih -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Pendapatan Penjualan</span>
                <span class="w-8 h-8 rounded-xl bg-blue-100/70 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                Rp {{ number_format($report['net_revenue'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">{{ $report['total_orders'] }} transaksi lunas</p>
        </div>

        <!-- Beban Bahan Baku -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Bahan Baku (HPP)</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100/70 text-[#c59b27] flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-rose-700 mt-3">
                Rp {{ number_format($report['raw_material_expense'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">{{ $report['raw_material_count'] }} pembelian bahan baku</p>
        </div>

        <!-- Beban Operasional & Gaji -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Beban Operasional & Gaji</span>
                <span class="w-8 h-8 rounded-xl bg-rose-100/70 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-rose-700 mt-3">
                Rp {{ number_format($report['total_operating_expenses'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Listrik, air, telepon & gaji</p>
        </div>

        <!-- Laba / Rugi Bersih -->
        <div class="rounded-2xl border p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]
            {{ $report['net_profit'] >= 0 ? 'bg-emerald-50/70 border-emerald-300' : 'bg-rose-50/70 border-rose-300' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider {{ $report['net_profit'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ $report['net_profit'] >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                </span>
                <span class="w-8 h-8 rounded-xl {{ $report['net_profit'] >= 0 ? 'bg-emerald-200 text-emerald-800' : 'bg-rose-200 text-rose-800' }} flex items-center justify-center font-bold">
                    @if($report['net_profit'] >= 0)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @endif
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-black mt-3 {{ $report['net_profit'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                Rp {{ number_format($report['net_profit'], 0, ',', '.') }}
            </p>
            <p class="text-xs mt-1 {{ $report['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $report['net_profit'] >= 0 ? 'Kondisi Finansial Surplus' : 'Kondisi Finansial Defisit' }}
            </p>
        </div>
    </div>

    <!-- Formal Financial Income Statement Table -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] overflow-hidden">
        <div class="p-5 border-b border-[#f0ece1] bg-[#faf8f5] flex items-center justify-between">
            <div>
                <h3 class="font-serif-classic text-lg font-bold text-[#262017]">Rincian Laporan Laba Rugi</h3>
                <p class="text-xs text-[#786e60]">Struktur perhitungan akuntansi laba rugi komersial Fira Cake.</p>
            </div>
            <span class="text-xs font-mono text-[#786e60]">Mata Uang: IDR (Rupiah)</span>
        </div>

        <div class="p-6 space-y-6 text-xs text-[#262017]">
            <!-- Bagian 1: Pendapatan -->
            <div>
                <h4 class="font-bold text-sm text-[#262017] uppercase tracking-wider pb-2 border-b border-[#e5dfd3]">
                    1. Pendapatan Penjualan (Revenue)
                </h4>
                <div class="divide-y divide-[#f5f2eb] mt-2">
                    <div class="flex justify-between py-2">
                        <span class="text-[#786e60]">Penjualan Bruto (Kotor)</span>
                        <span class="font-medium">Rp {{ number_format($report['gross_revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-[#786e60]">Potongan / Diskon Penjualan</span>
                        <span class="font-medium text-rose-600">- Rp {{ number_format($report['total_discounts'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2.5 bg-[#faf8f5] px-3 rounded-lg font-bold">
                        <span>Total Pendapatan Bersih (Net Sales)</span>
                        <span class="text-blue-800">Rp {{ number_format($report['net_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Beban Pokok Penjualan / Bahan Baku -->
            <div>
                <h4 class="font-bold text-sm text-[#262017] uppercase tracking-wider pb-2 border-b border-[#e5dfd3]">
                    2. Beban Pokok Produksi (Cost of Goods Sold)
                </h4>
                <div class="divide-y divide-[#f5f2eb] mt-2">
                    <div class="flex justify-between py-2">
                        <span class="text-[#786e60]">Pembelian Bahan Baku (Tepung, Telur, Cokelat, Susu, dll)</span>
                        <span class="font-medium text-rose-700">Rp {{ number_format($report['raw_material_expense'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2.5 bg-amber-50/60 px-3 rounded-lg font-bold border border-amber-200/60">
                        <span class="text-amber-900">LABA KOTOR (GROSS PROFIT)</span>
                        <span class="text-amber-900 text-sm">Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Beban Operasional -->
            <div>
                <h4 class="font-bold text-sm text-[#262017] uppercase tracking-wider pb-2 border-b border-[#e5dfd3]">
                    3. Beban Operasional & Penggajian (Operating Expenses)
                </h4>
                <div class="divide-y divide-[#f5f2eb] mt-2">
                    <div class="flex justify-between py-2">
                        <span class="text-[#786e60]">Beban Operasional Lainnya (Listrik, Air, Telepon/Internet, Gas)</span>
                        <span class="font-medium">Rp {{ number_format($report['operational_expense'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-[#786e60]">Beban Gaji Karyawan (Payroll Staf)</span>
                        <span class="font-medium">Rp {{ number_format($report['salary_expense'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2.5 bg-[#faf8f5] px-3 rounded-lg font-bold">
                        <span>Total Beban Operasional</span>
                        <span class="text-rose-700">Rp {{ number_format($report['total_operating_expenses'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Bagian 4: Hasil Laba/Rugi Bersih -->
            <div class="p-4 rounded-xl border-2 {{ $report['net_profit'] >= 0 ? 'bg-emerald-50 border-emerald-400' : 'bg-rose-50 border-rose-400' }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $report['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            Hasil Akhir Periode
                        </span>
                        <h3 class="font-serif-classic text-xl font-bold {{ $report['net_profit'] >= 0 ? 'text-emerald-900' : 'text-rose-900' }}">
                            {{ $report['net_profit'] >= 0 ? 'LABA BERSIH USAHA (NET PROFIT)' : 'RUGI BERSIH USAHA (NET LOSS)' }}
                        </h3>
                    </div>
                    <div class="text-left sm:text-right">
                        <div class="font-serif-classic text-2xl font-black {{ $report['net_profit'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                            Rp {{ number_format($report['net_profit'], 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] {{ $report['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            Margin Laba: {{ $report['net_revenue'] > 0 ? number_format(($report['net_profit'] / $report['net_revenue']) * 100, 1) : 0 }}% dari omzet
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
