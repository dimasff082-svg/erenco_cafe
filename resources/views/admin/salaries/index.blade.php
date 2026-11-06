@extends('layouts.app')

@php
    $isOwner = request()->routeIs('owner.*') || (auth()->check() && auth()->user()->isOwner());
    $routePrefix = $isOwner ? 'owner.salaries' : 'admin.salaries';
@endphp

@section('content')
<div class="space-y-6" x-data="salaryManagement()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Penggajian Karyawan</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Penginputan, penetapan rincian honor, dan pencetakan slip gaji resmi seluruh staf toko.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="openAddModal()" onclick="window.openSalaryAddModal && window.openSalaryAddModal()"
                class="inline-flex items-center gap-2 bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition active:scale-95">
                <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Input Slip Gaji Baru</span>
            </button>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p class="font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-sm font-bold">&times;</button>
        </div>
    @endif

    <!-- 4 Summary Cards (Tanpa Emoji, Menggunakan SVG Icons) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Gaji Terbayar -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Gaji Lunas Terbayar</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-emerald-800 mt-3">
                Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Total honor yang telah tersalurkan</p>
        </div>

        <!-- Total Pending -->
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
            <p class="text-xs text-[#786e60] mt-1">Menunggu konfirmasi pembayaran</p>
        </div>

        <!-- Akumulasi Beban Gaji -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Total Akumulasi Payroll</span>
                <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                Rp {{ number_format($summary['total_payroll'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Termasuk komponen laba/rugi toko</p>
        </div>

        <!-- Total Slip -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Jumlah Slip Terbit</span>
                <span class="w-8 h-8 rounded-xl bg-stone-100 text-stone-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                {{ $summary['total_slips'] }} Slip
            </p>
            <p class="text-xs text-[#786e60] mt-1">Arsip resmi penggajian Fira Cake</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
        <form method="GET" action="{{ request()->routeIs('owner.*') ? route('owner.salaries.index') : route('admin.salaries.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Cari No. Slip / Periode</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Contoh: PAY-2026 atau November..."
                    class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Filter Karyawan</label>
                <select name="user_id" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->roleLabel() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Status Pembayaran</label>
                <select name="payment_status" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-[#262017] hover:bg-black text-white text-xs font-semibold transition">
                    Terapkan
                </button>
                <a href="{{ request()->routeIs('owner.*') ? route('owner.salaries.index') : route('admin.salaries.index') }}" class="py-2 px-3 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-stone-100 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#f0ece1] flex items-center justify-between bg-[#faf8f5]">
            <h3 class="font-serif-classic text-base font-bold text-[#262017]">Daftar Slip Penggajian</h3>
            <span class="text-xs text-[#786e60]">Diurutkan berdasarkan tanggal pembayaran terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#f0ece1] bg-[#faf8f5]/60 text-[#786e60] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Slip</th>
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4 text-right">Gaji Pokok</th>
                        <th class="py-3 px-4 text-right">Tunjangan</th>
                        <th class="py-3 px-4 text-right">Potongan</th>
                        <th class="py-3 px-4 text-right">Gaji Bersih</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f5f2eb]">
                    @forelse($salaries as $sal)
                        <tr class="hover:bg-[#fcfaf7] transition">
                            <td class="py-3 px-4 font-mono font-bold text-[#262017]">
                                {{ $sal->payroll_number }}
                            </td>
                            <td class="py-3 px-4 font-medium text-[#262017]">
                                {{ $sal->period_month }}
                                <div class="text-[10px] text-[#786e60]">Bayar: {{ $sal->payment_date?->format('d M Y') ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-[#262017]">
                                <div class="font-bold">{{ $sal->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-[#786e60]">{{ $sal->user->roleLabel() ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-[#262017]">
                                Rp {{ number_format($sal->basic_salary, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-700">
                                + Rp {{ number_format($sal->allowance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-rose-700">
                                - Rp {{ number_format($sal->deductions, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-800 text-sm">
                                Rp {{ number_format($sal->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($sal->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                                        </svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Cetak -->
                                    <a href="{{ route($routePrefix . '.print', $sal) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#faf8f5] hover:bg-[#e5dfd3] text-[#262017] border border-[#d6cfc2] font-semibold transition text-[11px]"
                                        title="Cetak Slip Gaji">
                                        <svg class="w-3.5 h-3.5 text-[#786e60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>Cetak</span>
                                    </a>

                                    <!-- Tombol Edit -->
                                    <button type="button"
                                        @click="openEditModal({{ json_encode($sal) }})"
                                        class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                                        title="Edit Slip Gaji">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <form method="POST" action="{{ route($routePrefix . '.destroy', $sal) }}"
                                        data-confirm="Apakah Anda yakin ingin menghapus arsip slip gaji {{ $sal->payroll_number }} ({{ $sal->user->name ?? 'Karyawan' }})?"
                                        data-confirm-title="Hapus Slip Gaji"
                                        data-confirm-btn="Ya, Hapus Slip Gaji"
                                        data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:text-rose-900 hover:bg-rose-50 transition" title="Hapus Slip Gaji">
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
                            <td colspan="9" class="py-12 text-center text-[#786e60]">
                                <svg class="w-10 h-10 text-stone-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-medium">Belum ada catatan slip gaji yang diinput.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($salaries->hasPages())
            <div class="p-4 border-t border-[#f0ece1] bg-[#faf8f5]/50">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Input Slip Gaji Baru -->
    <div x-show="showAddModal" id="addSalaryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-stone-900/70 backdrop-blur-xs transition-opacity" @click="showAddModal = false"></div>

        <!-- Centered Dialog Container -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-xl border border-[#e5dfd3] pointer-events-auto" @click.stop>
                <form method="POST" action="{{ route($routePrefix . '.store') }}">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-[#f0ece1]">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#c59b27]">Form Penggajian Karyawan</span>
                                <h3 class="font-serif-classic text-xl font-bold text-[#262017]">Input Slip Gaji Baru</h3>
                            </div>
                            <button type="button" @click="showAddModal = false" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <!-- Pilih Karyawan -->
                            <div>
                                <label class="block text-xs font-bold text-[#262017] mb-1">
                                    Pilih Karyawan Penerima Gaji <span class="text-rose-600">*</span>
                                </label>
                                <select name="user_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                    <option value="">-- Pilih Staf Toko --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                             {{ $emp->name }} — {{ $emp->roleLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Periode Bulan -->
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">
                                        Periode Bulan <span class="text-rose-600">*</span>
                                    </label>
                                    <input type="text" name="period_month" required
                                        x-model="form.period_month"
                                        placeholder="Contoh: November 2026"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                </div>

                                <!-- Tanggal Pembayaran -->
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">
                                        Tanggal Pembayaran <span class="text-rose-600">*</span>
                                    </label>
                                    <input type="date" name="payment_date" required
                                        x-model="form.payment_date"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                </div>
                            </div>

                            <!-- Komponen Gaji: Pokok, Tunjangan, Potongan (Auto Pemisah Titik Ribuan) -->
                            <div class="bg-[#faf8f5] p-4 rounded-xl border border-[#e5dfd3] space-y-3">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-[#786e60]">Rincian Komponen Honor</div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-[#262017] mb-1">Gaji Pokok (Rp) *</label>
                                        <input type="text" inputmode="numeric" required
                                            x-model="form.basic_salary_display"
                                            @input="handleSalaryInput('basic_salary', 'form')"
                                            placeholder="3.000.000"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <input type="hidden" name="basic_salary" :value="form.basic_salary">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-emerald-700 mb-1">Tunjangan / Bonus (Rp)</label>
                                        <input type="text" inputmode="numeric"
                                            x-model="form.allowance_display"
                                            @input="handleSalaryInput('allowance', 'form')"
                                            placeholder="0"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <input type="hidden" name="allowance" :value="form.allowance">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-rose-700 mb-1">Potongan (Rp)</label>
                                        <input type="text" inputmode="numeric"
                                            x-model="form.deductions_display"
                                            @input="handleSalaryInput('deductions', 'form')"
                                            placeholder="0"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-rose-500">
                                        <input type="hidden" name="deductions" :value="form.deductions">
                                    </div>
                                </div>

                                <!-- Live Net Calculation -->
                                <div class="pt-2 border-t border-[#e5dfd3] flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#262017]">Total Gaji Bersih (Take Home Pay):</span>
                                    <span class="text-base font-serif-classic font-black text-emerald-800" x-text="'Rp ' + formatRupiah(calculateNet())"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Status Pembayaran -->
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Status Pembayaran</label>
                                    <select name="payment_status" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <option value="paid">Lunas (Telah Dibayarkan)</option>
                                        <option value="pending">Pending (Belum Dibayar)</option>
                                    </select>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Metode Pembayaran</label>
                                    <select name="payment_method" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="cash">Uang Tunai (Cash)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="block text-xs font-medium text-[#786e60] mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea name="notes" rows="2" placeholder="Keterangan lembur / bonus penjualan..."
                                    class="w-full text-xs p-3 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-[#faf8f5] border-t border-[#f0ece1] flex items-center justify-end gap-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-white">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold shadow-xs transition">
                            Simpan & Terbitkan Slip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Slip Gaji -->
    <div x-show="showEditModal" id="editSalaryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-stone-900/70 backdrop-blur-xs transition-opacity" @click="showEditModal = false"></div>

        <!-- Centered Dialog Container -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-xl border border-[#e5dfd3] pointer-events-auto" @click.stop>
                <form method="POST" :action="'/{{ $isOwner ? 'owner' : 'admin' }}/salaries/' + editForm.id">
                    @csrf
                    @method('PUT')
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-[#f0ece1]">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#c59b27]" x-text="editForm.payroll_number"></span>
                                <h3 class="font-serif-classic text-xl font-bold text-[#262017]">Edit Slip Gaji</h3>
                            </div>
                            <button type="button" @click="showEditModal = false" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Periode Bulan *</label>
                                    <input type="text" name="period_month" required x-model="editForm.period_month"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Tanggal Pembayaran *</label>
                                    <input type="date" name="payment_date" required x-model="editForm.payment_date"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                </div>
                            </div>

                            <div class="bg-[#faf8f5] p-4 rounded-xl border border-[#e5dfd3] space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-[#262017] mb-1">Gaji Pokok (Rp) *</label>
                                        <input type="text" inputmode="numeric" required
                                            x-model="editForm.basic_salary_display"
                                            @input="handleSalaryInput('basic_salary', 'editForm')"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <input type="hidden" name="basic_salary" :value="editForm.basic_salary">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-emerald-700 mb-1">Tunjangan (Rp)</label>
                                        <input type="text" inputmode="numeric"
                                            x-model="editForm.allowance_display"
                                            @input="handleSalaryInput('allowance', 'editForm')"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <input type="hidden" name="allowance" :value="editForm.allowance">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-rose-700 mb-1">Potongan (Rp)</label>
                                        <input type="text" inputmode="numeric"
                                            x-model="editForm.deductions_display"
                                            @input="handleSalaryInput('deductions', 'editForm')"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-rose-500">
                                        <input type="hidden" name="deductions" :value="editForm.deductions">
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-[#e5dfd3] flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#262017]">Total Gaji Bersih:</span>
                                    <span class="text-base font-serif-classic font-black text-emerald-800"
                                        x-text="'Rp ' + formatRupiah(calculateEditNet())"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Status Pembayaran</label>
                                    <select name="payment_status" x-model="editForm.payment_status" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <option value="paid">Lunas (Telah Dibayarkan)</option>
                                        <option value="pending">Pending (Belum Dibayar)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#262017] mb-1">Metode Pembayaran</label>
                                    <select name="payment_method" x-model="editForm.payment_method" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="cash">Uang Tunai (Cash)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#786e60] mb-1">Catatan</label>
                                <textarea name="notes" rows="2" x-model="editForm.notes"
                                    class="w-full text-xs p-3 rounded-xl border border-[#d6cfc2] bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-[#faf8f5] border-t border-[#f0ece1] flex items-center justify-end gap-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-white">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#262017] hover:bg-black text-white text-xs font-bold shadow-xs transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function formatRupiahDisplay(num) {
    if (num === null || num === undefined || num === '') return '';
    const clean = num.toString().replace(/[^0-9]/g, '');
    if (!clean) return '';
    return new Intl.NumberFormat('id-ID').format(clean);
}

function salaryManagement() {
    return {
        showAddModal: false,
        showEditModal: false,
        form: {
            period_month: '{{ now()->translatedFormat("F Y") }}',
            payment_date: '{{ now()->toDateString() }}',
            basic_salary: 3000000,
            basic_salary_display: '3.000.000',
            allowance: 0,
            allowance_display: '0',
            deductions: 0,
            deductions_display: '0',
        },
        editForm: {
            id: null,
            payroll_number: '',
            period_month: '',
            payment_date: '',
            basic_salary: 0,
            basic_salary_display: '',
            allowance: 0,
            allowance_display: '',
            deductions: 0,
            deductions_display: '',
            payment_status: 'paid',
            payment_method: 'transfer',
            notes: '',
        },

        init() {
            window.__salaryApp = this;
        },

        openAddModal() {
            this.showAddModal = true;
        },

        openEditModal(salary) {
            this.editForm = {
                id: salary.id,
                payroll_number: salary.payroll_number,
                period_month: salary.period_month,
                payment_date: salary.payment_date ? salary.payment_date.split('T')[0] : '',
                basic_salary: Number(salary.basic_salary) || 0,
                basic_salary_display: formatRupiahDisplay(salary.basic_salary),
                allowance: Number(salary.allowance) || 0,
                allowance_display: formatRupiahDisplay(salary.allowance),
                deductions: Number(salary.deductions) || 0,
                deductions_display: formatRupiahDisplay(salary.deductions),
                payment_status: salary.payment_status,
                payment_method: salary.payment_method,
                notes: salary.notes || '',
            };
            this.showEditModal = true;
        },

        handleSalaryInput(field, targetScope) {
            const scope = targetScope === 'form' ? this.form : this.editForm;
            const rawDigits = (scope[field + '_display'] || '').toString().replace(/[^0-9]/g, '');
            const num = rawDigits ? parseInt(rawDigits, 10) : 0;
            scope[field] = num;
            scope[field + '_display'] = rawDigits ? new Intl.NumberFormat('id-ID').format(rawDigits) : '';
        },

        calculateNet() {
            const basic = Number(this.form.basic_salary) || 0;
            const allow = Number(this.form.allowance) || 0;
            const deduct = Number(this.form.deductions) || 0;
            return basic + allow - deduct;
        },

        calculateEditNet() {
            const basic = Number(this.editForm.basic_salary) || 0;
            const allow = Number(this.editForm.allowance) || 0;
            const deduct = Number(this.editForm.deductions) || 0;
            return basic + allow - deduct;
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number || 0);
        }
    };
}

window.openSalaryAddModal = function() {
    if (window.__salaryApp) {
        window.__salaryApp.openAddModal();
    } else {
        const modal = document.getElementById('addSalaryModal');
        if (modal) modal.style.display = 'block';
    }
};
</script>
@endsection

