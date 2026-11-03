@extends('layouts.app')

@php
    $isOwner = request()->routeIs('owner.*') || (auth()->check() && auth()->user()->isOwner());
    $attendanceIndexRoute = $isOwner ? route('owner.attendances.index') : route('admin.attendances.index');
@endphp

@section('content')
<div class="space-y-6" x-data="{ previewPhotoUrl: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Rekapitulasi Presensi Karyawan</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Monitoring catatan kehadiran staf (Kasir, Warehouse, dll) dengan verifikasi jarak GPS dan foto selfie.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-100 border border-stone-200 text-stone-700 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Radius Maksimal: 50 Meter</span>
            </span>
        </div>
    </div>

    <!-- 4 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Karyawan Masuk Hari Ini -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Karyawan Hadir Hari Ini</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100/70 text-[#c59b27] flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                {{ $summary['today_employees'] }} Orang
            </p>
            <p class="text-xs text-[#786e60] mt-1">Staf unik yang telah melakukan presensi</p>
        </div>

        <!-- Total Presensi Tercatat -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Aktivitas Presensi</span>
                <span class="w-8 h-8 rounded-xl bg-blue-100/70 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-[#262017] mt-3">
                {{ $summary['today_total'] }} Catatan
            </p>
            <p class="text-xs text-[#786e60] mt-1">Akumulasi shift (pagi, istirahat, pulang)</p>
        </div>

        <!-- Sesuai Radius (<= 50m) -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Presensi Valid (<= 50m)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100/70 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-emerald-700 mt-3">
                {{ $summary['today_valid'] }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Presensi terverifikasi di area kantor</p>
        </div>

        <!-- Di Luar Radius (> 50m) -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786e60]">Luar Radius (> 50m)</span>
                <span class="w-8 h-8 rounded-xl bg-rose-100/70 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <p class="font-serif-classic text-2xl font-bold text-rose-700 mt-3">
                {{ $summary['today_invalid'] }}
            </p>
            <p class="text-xs text-[#786e60] mt-1">Terdeteksi di luar zona kantor</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)]">
        <form method="GET" action="{{ $attendanceIndexRoute }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Filter Periode Preset -->
            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Periode</label>
                <select name="period" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                    <option value="today" {{ request('period', 'today') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="this_week" {{ request('period') === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ request('period') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="all" {{ request('period') === 'all' ? 'selected' : '' }}>Semua Periode</option>
                </select>
            </div>

            <!-- Filter Karyawan -->
            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Karyawan</label>
                <select name="user_id" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->roleLabel() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenis Shift -->
            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Jadwal Shift</label>
                <select name="type" class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                    <option value="">Semua Shift</option>
                    <option value="check_in" {{ request('type') === 'check_in' ? 'selected' : '' }}>Absen Pagi</option>
                    <option value="break_start" {{ request('type') === 'break_start' ? 'selected' : '' }}>Mulai Istirahat</option>
                    <option value="break_end" {{ request('type') === 'break_end' ? 'selected' : '' }}>Selesai Istirahat</option>
                    <option value="check_out" {{ request('type') === 'check_out' ? 'selected' : '' }}>Absen Pulang</option>
                </select>
            </div>

            <!-- Filter Tanggal Manual -->
            <div>
                <label class="block text-[11px] font-bold text-[#786e60] uppercase mb-1">Tanggal Spesifik</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-[#262017] hover:bg-black text-white text-xs font-semibold transition">
                    Terapkan Filter
                </button>
                <a href="{{ $attendanceIndexRoute }}" class="py-2 px-3 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-stone-100 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#f0ece1] bg-[#faf8f5]/60 text-[#786e60] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Jadwal Shift</th>
                        <th class="py-3 px-4">Jarak dari Kantor</th>
                        <th class="py-3 px-4">Koordinat GPS</th>
                        <th class="py-3 px-4 text-center">Foto Kehadiran</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f5f2eb]">
                    @forelse($attendances as $item)
                        <tr class="hover:bg-[#fcfaf7] transition">
                            <td class="py-3 px-4 text-[#262017]">
                                <div class="font-bold">{{ $item->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-[#786e60]">{{ $item->user->roleLabel() ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-medium text-[#262017]">
                                <div>{{ $item->date?->format('d M Y') ?? '-' }}</div>
                                <div class="text-[10px] font-mono text-[#786e60]">{{ \Carbon\Carbon::parse($item->time)->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold
                                    {{ $item->type === 'check_in' ? 'bg-amber-100 text-amber-900' : '' }}
                                    {{ $item->type === 'break_start' ? 'bg-blue-100 text-blue-900' : '' }}
                                    {{ $item->type === 'break_end' ? 'bg-indigo-100 text-indigo-900' : '' }}
                                    {{ $item->type === 'check_out' ? 'bg-emerald-100 text-emerald-900' : '' }}">
                                    {{ $item->typeLabel() }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 font-semibold {{ $item->isWithinRadius() ? 'text-emerald-700' : 'text-rose-700' }}">
                                    <span>{{ number_format($item->distance_meters, 1) }} m</span>
                                    <span class="text-[10px]">({{ $item->isWithinRadius() ? '<= 50m' : '> 50m' }})</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-[#786e60]">
                                {{ $item->latitude }}, {{ $item->longitude }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->photo_path)
                                    <button type="button" @click="previewPhotoUrl = '{{ asset('storage/' . $item->photo_path) }}'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#faf8f5] hover:bg-[#e5dfd3] border border-[#d6cfc2] font-semibold transition text-[11px] text-[#262017]">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Lihat Foto</span>
                                    </button>
                                @else
                                    <span class="text-[#786e60]">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Hadir
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-[#786e60]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <p class="font-medium">Tidak ada data presensi yang sesuai dengan filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-[#f0ece1] bg-[#faf8f5]/50">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Preview Foto -->
    <div x-show="previewPhotoUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75" style="display: none;">
        <div class="relative bg-white rounded-2xl max-w-md w-full overflow-hidden p-3" @click.away="previewPhotoUrl = null">
            <div class="flex justify-between items-center pb-2 border-b border-stone-200 mb-2">
                <span class="text-xs font-bold text-[#262017]">Foto Bukti Kehadiran</span>
                <button type="button" @click="previewPhotoUrl = null" class="text-stone-400 hover:text-stone-700 text-lg font-bold">&times;</button>
            </div>
            <img :src="previewPhotoUrl" class="w-full h-auto rounded-xl object-cover max-h-[70vh]">
        </div>
    </div>
</div>
@endsection
