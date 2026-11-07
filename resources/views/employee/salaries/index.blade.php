@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Slip Gaji Saya</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Riwayat honor, rincian komponen gaji, dan arsip unduhan slip gaji resmi Anda di {{ $storeName }}.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-semibold">
                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ $user->name }} ({{ $user->roleLabel() }})</span>
            </span>
        </div>
    </div>

    <!-- 4 Ringkasan Cards (Tanpa Emoji, SVG Icons Saja) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Diterima -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Gaji Diterima (Lunas)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-emerald-800 mt-3">
                Rp {{ number_format($summary['total_received'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Akumulasi gaji yang telah ditransfer/diterima</p>
        </div>

        <!-- Gaji Pending -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Gaji Pending</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-amber-800 mt-3">
                Rp {{ number_format($summary['total_pending'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Menunggu proses pencairan dari manajemen</p>
        </div>

        <!-- Jumlah Slip Terbit -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Jumlah Slip Terbit</span>
                <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                {{ $summary['total_slips'] }} Slip
            </p>
            <p class="text-xs text-[#786e60] mt-1">Total riwayat penggajian tercatat</p>
        </div>

        <!-- Periode Terakhir -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Periode Terakhir</span>
                <span class="w-8 h-8 rounded-xl bg-stone-100 text-[#786e60] flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-lg font-bold text-[#262017] mt-3 truncate">
                {{ $summary['latest_slip'] ? $summary['latest_slip']->period_month : '-' }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">
                @if($summary['latest_slip'])
                    Tgl: {{ $summary['latest_slip']->payment_date?->format('d/m/Y') ?? '-' }}
                @else
                    Belum ada slip
                @endif
            </p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 sm:p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
        <form method="GET" action="{{ route('my_salaries.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-6 lg:col-span-5">
                <label class="block text-xs font-bold text-[#786e60] uppercase tracking-wider mb-1">Cari Slip Gaji</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nomor slip (PAY-...) atau bulan..."
                        class="w-full pl-9 pr-4 py-2 bg-[#faf8f5] border border-[#e5dfd3] rounded-xl text-xs text-[#262017] focus:outline-none focus:ring-2 focus:ring-[#c59b27]/30 focus:border-[#c59b27]">
                    <svg class="w-4 h-4 text-[#786e60] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="sm:col-span-4 lg:col-span-4">
                <label class="block text-xs font-bold text-[#786e60] uppercase tracking-wider mb-1">Status Pembayaran</label>
                <select name="payment_status"
                    class="w-full px-3 py-2 bg-[#faf8f5] border border-[#e5dfd3] rounded-xl text-xs text-[#262017] focus:outline-none focus:ring-2 focus:ring-[#c59b27]/30 focus:border-[#c59b27]">
                    <option value="">Semua Status Pembayaran</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas (Telah Dibayarkan)</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-2">
                <button type="submit"
                    class="w-full bg-[#262017] hover:bg-black text-white text-xs font-bold py-2 px-4 rounded-xl transition">
                    Terapkan
                </button>
                @if(request()->hasAny(['search', 'payment_status']))
                    <a href="{{ route('my_salaries.index') }}"
                        class="px-3 py-2 bg-[#faf8f5] hover:bg-[#eae4d7] text-[#786e60] text-xs font-semibold rounded-xl border border-[#e5dfd3] transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Slip Gaji -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#e5dfd3] flex items-center justify-between">
            <div>
                <h3 class="font-serif-classic text-base font-bold text-[#262017]">Daftar Slip Gaji Saya</h3>
                <p class="text-xs text-[#786e60] mt-0.5">Seluruh arsip honor bulanan yang dapat Anda lihat dan unduh dalam format PDF.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#faf8f5] border border-[#e5dfd3] text-[#786e60]">
                {{ $salaries->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#faf8f5] border-b border-[#e5dfd3] text-[#786e60] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Slip</th>
                        <th class="py-3 px-4">Periode Bulan</th>
                        <th class="py-3 px-4">Tanggal Bayar</th>
                        <th class="py-3 px-4 text-right">Gaji Pokok</th>
                        <th class="py-3 px-4 text-right">Tunjangan</th>
                        <th class="py-3 px-4 text-right">Insentif</th>
                        <th class="py-3 px-4 text-right">Potongan</th>
                        <th class="py-3 px-4 text-right">Gaji Bersih</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi Unduh / Cetak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f2ece1] text-[#262017]">
                    @forelse($salaries as $salary)
                        <tr class="hover:bg-[#faf8f5]/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-900">
                                {{ $salary->payroll_number }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold">
                                {{ $salary->period_month }}
                            </td>
                            <td class="py-3.5 px-4 text-[#786e60]">
                                {{ $salary->payment_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium">
                                Rp {{ number_format($salary->basic_salary, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-emerald-700 font-medium">
                                + Rp {{ number_format($salary->allowance, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-amber-700 font-medium">
                                + Rp {{ number_format($salary->incentive ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-rose-700 font-medium">
                                - Rp {{ number_format($salary->deductions, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-800 text-sm">
                                Rp {{ number_format($salary->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($salary->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3" />
                                        </svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Unduh PDF -->
                                    <a href="{{ route('my_salaries.print', [$salary, 'download' => 'pdf']) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#c59b27] hover:bg-[#a98119] text-stone-950 font-bold text-xs shadow-xs transition active:scale-95"
                                        title="Unduh Slip Gaji dalam format PDF">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>Unduh PDF</span>
                                    </a>

                                    <!-- Tombol Cetak / Lihat -->
                                    <a href="{{ route('my_salaries.print', $salary) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#faf8f5] hover:bg-[#ede5d6] text-[#262017] border border-[#e5dfd3] font-semibold text-xs transition active:scale-95"
                                        title="Buka Pratinjau & Cetak Slip Gaji">
                                        <svg class="w-3.5 h-3.5 text-[#786e60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>Cetak</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-[#786e60]">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#faf8f5] border border-[#e5dfd3] flex items-center justify-center text-[#786e60] mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-sm text-[#262017]">Belum Ada Slip Gaji</p>
                                <p class="text-xs text-[#786e60] mt-1 max-w-sm mx-auto">
                                    Belum ada data slip gaji yang diterbitkan untuk akun Anda. Hubungi Administrator atau Pemilik untuk informasi penggajian.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($salaries->hasPages())
            <div class="p-4 border-t border-[#e5dfd3] bg-[#faf8f5]">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

