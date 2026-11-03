@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #attendance-map {
        height: 340px;
        width: 100%;
        border-radius: 1rem;
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.75rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="attendanceManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Presensi Kehadiran Karyawan</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Absensi mandiri harian dengan verifikasi live foto selfie kamera dan radius lokasi kantor maksimal 50 meter.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Lokasi GPS Badge -->
            <template x-if="geoStatus === 'locating'">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-100 border border-stone-200 text-stone-700 text-xs font-semibold animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Mendeteksi GPS...</span>
                </span>
            </template>
            <template x-if="geoStatus === 'ok' && distanceMeters <= 50">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span x-text="'Lokasi Sesuai (' + distanceMeters.toFixed(1) + ' m <= 50 m)'"></span>
                </span>
            </template>
            <template x-if="geoStatus === 'ok' && distanceMeters > 50">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span x-text="'Di Luar Radius (' + distanceMeters.toFixed(1) + ' m > 50 m)'"></span>
                </span>
            </template>

            <!-- Tombol Refresh / Set Lokasi Kantor (Dev & Toko) -->
            <button type="button" @click="useOfficeLocation()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#faf7f0] hover:bg-[#ede7da] border border-[#dcd4c5] text-[#4d4436] text-xs font-bold transition shadow-2xs"
                title="Gunakan koordinat resmi kantor toko Fira Cake (0 meter)">
                <svg class="w-3.5 h-3.5 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Set Lokasi Kantor</span>
            </button>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ is_array(session('success')) ? implode(', ', session('success')) : session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <!-- Peta Pemantauan Titik Kantor & Radius 50 Meter -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#f0ece1]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="distanceMeters <= 50 ? 'bg-emerald-500' : 'bg-rose-500 animate-pulse'"></span>
                    <h3 class="font-serif-classic text-base font-bold text-[#262017]">
                        Peta Radius Presensi Kantor (Maks. 50 Meter)
                    </h3>
                </div>
                <p class="text-xs text-[#786e60] mt-0.5">
                    Titik Kantor: <span class="font-bold text-[#262017]">BISOLPIN, Ruko Ruby, Jl. Boulevard Rubi II, Masale, Panakkukang, Makassar</span>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="requestLocation()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Cek Ulang GPS</span>
                </button>
                <button type="button" @click="useOfficeLocation()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold transition shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Set Posisi ke Kantor (0 m)</span>
                </button>
                <a href="https://maps.app.goo.gl/8UpiFYzVZgmKkhmi6?g_st=ac" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Buka Google Maps</span>
                </a>
            </div>
        </div>

        <!-- 3 Info Box Jarak & Koordinat -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-3 rounded-xl border transition" :class="distanceMeters <= 50 ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-rose-50 border-rose-300 text-rose-900'">
                <div class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Status Kelayakan Absensi</div>
                <div class="text-xs font-black mt-1 flex items-center gap-1.5">
                    <span x-show="distanceMeters <= 50" class="text-emerald-700">✓ Memenuhi Syarat (Dalam Radius <= 50m)</span>
                    <span x-show="distanceMeters > 50" class="text-rose-700">✗ Di Luar Radius (Lebih dari 50 meter)</span>
                </div>
            </div>
            <div class="p-3 rounded-xl border border-[#e5dfd3] bg-[#faf8f5]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#786e60]">Jarak dari Kantor Toko</div>
                <div class="text-sm font-black font-mono text-[#262017] mt-1" x-text="distanceMeters.toFixed(1) + ' Meter'"></div>
            </div>
            <div class="p-3 rounded-xl border border-[#e5dfd3] bg-[#faf8f5]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#786e60]">Koordinat Anda Terdeteksi</div>
                <div class="text-xs font-mono font-bold text-[#262017] mt-1" x-text="userLat.toFixed(6) + ', ' + userLng.toFixed(6)"></div>
            </div>
        </div>

        <!-- Canvas Map Leaflet -->
        <div id="attendance-map" class="shadow-inner border border-[#dcd4c5]"></div>
    </div>

    <!-- 4 Bagian Jadwal Absen Hari Ini -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-serif-classic text-lg font-bold text-[#262017]">Jadwal 4 Bagian Presensi Hari Ini</h3>
                <p class="text-xs text-[#786e60]">Lakukan presensi sesuai dengan jadwal kerja Anda (Pagi, Istirahat, Selesai Istirahat, Pulang).</p>
            </div>
            <span class="text-xs text-[#786e60] font-medium hidden sm:block">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($shiftSlots as $typeKey => $shiftItem)
            @php
                $attendance = $todayAttendances->get($typeKey);
                $isCompleted = (bool) $attendance;
            @endphp
            <div class="rounded-2xl border p-5 transition relative overflow-hidden flex flex-col justify-between
                {{ $isCompleted ? 'bg-emerald-50/40 border-emerald-300' : 'bg-white border-[#e5dfd3] shadow-xs' }}">
                
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $shiftItem['color'] === 'amber' ? 'bg-amber-100 text-amber-800' : ($shiftItem['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : ($shiftItem['color'] === 'indigo' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800')) }}">
                            @if(($shiftItem['icon_type'] ?? '') === 'sun')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            @elseif(($shiftItem['icon_type'] ?? '') === 'coffee')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3m4-3v3m4-3v3" />
                                </svg>
                            @elseif(($shiftItem['icon_type'] ?? '') === 'bolt')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            @endif
                        </div>
                        @if($isCompleted)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Sudah Absen</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600">
                                Belum Absen
                            </span>
                        @endif
                    </div>

                    <h4 class="font-serif-classic text-base font-bold text-[#262017]">
                        {{ $shiftItem['name'] }}
                    </h4>
                    <p class="text-[11px] text-[#786e60] mt-0.5 leading-snug">
                        {{ $shiftItem['description'] }}
                    </p>
                </div>

                <div class="mt-5 pt-3 border-t border-[#f0ece1]">
                    @if($isCompleted)
                        <div class="space-y-1.5 text-xs text-[#262017]">
                            <div class="flex items-center justify-between font-mono font-bold">
                                <span class="text-[#786e60] font-normal">Waktu:</span>
                                <span>{{ \Carbon\Carbon::parse($attendance->time)->format('H:i:s') }} WITA</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#786e60]">Jarak:</span>
                                <span class="font-semibold text-emerald-700">{{ number_format($attendance->distance_meters, 1) }} m</span>
                            </div>
                            @if($attendance->photo_path)
                                <div class="mt-2 pt-2 border-t border-emerald-200/60 flex items-center justify-between">
                                    <span class="text-[11px] text-[#786e60]">Foto Bukti:</span>
                                    <button type="button" @click="openPhotoPreview('{{ asset('storage/' . $attendance->photo_path) }}')"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-[#c59b27] hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Lihat Foto</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <button type="button"
                            @click="openModal('{{ $typeKey }}', '{{ $shiftItem['name'] }}')"
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold transition shadow-xs active:scale-95
                                {{ $shiftItem['color'] === 'amber' ? 'bg-[#c59b27] hover:bg-[#a98119] text-stone-950' : 'bg-[#262017] hover:bg-black text-white' }}">
                            <span>Lakukan Absen</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
        </div>
    </div>

    <!-- Riwayat Absensi Terakhir -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(38,32,23,0.05)] overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#f0ece1] flex items-center justify-between bg-[#faf8f5]">
            <div>
                <h3 class="font-serif-classic text-base font-bold text-[#262017]">Riwayat Presensi Anda</h3>
                <p class="text-xs text-[#786e60]">Catatan presensi harian yang telah tersimpan dalam sistem.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#f0ece1] bg-[#faf8f5]/60 text-[#786e60] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Jadwal Shift</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Jarak dari Kantor</th>
                        <th class="py-3 px-4 text-center">Foto Kamera</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f5f2eb]">
                    @forelse($history as $item)
                        <tr class="hover:bg-[#fcfaf7] transition">
                            <td class="py-3 px-4 font-medium text-[#262017]">
                                {{ $item->date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-[#262017]">
                                {{ $item->typeLabel() }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[#262017]">
                                {{ \Carbon\Carbon::parse($item->time)->format('H:i:s') }} WITA
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 font-semibold {{ $item->isWithinRadius() ? 'text-emerald-700' : 'text-rose-700' }}">
                                    <span>{{ number_format($item->distance_meters, 1) }} m</span>
                                    <span class="text-[10px]">({{ $item->isWithinRadius() ? '<= 50m' : '> 50m' }})</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->photo_path)
                                    <button type="button" @click="openPhotoPreview('{{ asset('storage/' . $item->photo_path) }}')"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-[#c59b27] hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Buka Foto</span>
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
                            <td colspan="6" class="py-12 text-center text-[#786e60]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <p class="font-medium">Belum ada catatan riwayat absensi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
            <div class="p-4 border-t border-[#f0ece1] bg-[#faf8f5]/50">
                {{ $history->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Absensi (Bebas Konflik Klik & Khusus Live Kamera) -->
    <div id="attendance-modal" x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-stone-900/70 backdrop-blur-xs transition-opacity" @click="closeModal()"></div>

        <!-- Centered Dialog Container -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-lg border border-[#e5dfd3]" @click.stop>
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="type" :value="selectedType" id="attendanceInputType">
                    <input type="hidden" name="latitude" :value="userLat" value="{{ $office['latitude'] }}" id="attendanceInputLat">
                    <input type="hidden" name="longitude" :value="userLng" value="{{ $office['longitude'] }}" id="attendanceInputLng">
                    <input type="hidden" name="photo_camera" x-ref="photoCameraInput" id="attendanceInputCamera">

                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-[#f0ece1]">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#c59b27]">Form Presensi Kehadiran</span>
                                <h3 class="font-serif-classic text-xl font-bold text-[#262017]" x-text="selectedTypeName" id="attendanceModalTitle"></h3>
                            </div>
                            <button type="button" @click="closeModal()" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
                        </div>

                        <!-- Distance Banner -->
                        <div class="mt-4 p-3 rounded-xl border flex items-center justify-between text-xs transition"
                            :class="distanceMeters <= 50 ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-rose-50 border-rose-300 text-rose-800'">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#c59b27] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div>
                                    <span class="font-bold">Jarak:</span>
                                    <span x-text="distanceMeters.toFixed(1) + ' m dari kantor Fira Cake'"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold" x-text="distanceMeters <= 50 ? 'Valid (<= 50m)' : 'Luar Radius (> 50m)'"></span>
                                <button type="button" @click="useOfficeLocation()"
                                    class="text-[10px] px-2 py-0.5 rounded bg-white border border-[#dcd4c5] font-semibold text-stone-700 hover:bg-stone-100">
                                    Set 0 m
                                </button>
                            </div>
                        </div>

                        <!-- Live Camera Selfie ONLY (Tidak boleh dari file perangkat) -->
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-[#262017]">
                                    Foto Bukti Kehadiran (Live Selfie Kamera) <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                    Hanya Kamera Langsung
                                </span>
                            </div>

                            <!-- Live Camera Preview / Captured Photo -->
                            <div class="relative bg-stone-900 rounded-xl overflow-hidden aspect-4/3 flex items-center justify-center border border-stone-700">
                                <video x-ref="videoElement" autoplay playsinline class="w-full h-full object-cover" :class="{'hidden': capturedImage}"></video>
                                <img :src="capturedImage" x-show="capturedImage" class="w-full h-full object-cover">
                                
                                <canvas x-ref="canvasElement" class="hidden"></canvas>

                                <div x-show="!cameraActive && !capturedImage" class="text-center p-4 text-white">
                                    <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-stone-800 flex items-center justify-center text-stone-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs text-stone-300">Kamera live belum aktif</p>
                                    <button type="button" @click="startCamera()" class="mt-2 px-3 py-1.5 rounded-lg bg-[#c59b27] text-stone-950 text-xs font-bold hover:bg-[#a98119] transition">
                                        Buka Kamera
                                    </button>
                                </div>

                                <div x-show="cameraActive && !capturedImage" class="absolute bottom-3 inset-x-0 flex justify-center">
                                    <button type="button" @click="takeSnapshot()" class="px-4 py-2 rounded-xl bg-white text-stone-900 text-xs font-bold shadow-lg hover:bg-stone-100 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-stone-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Ambil Foto Selfie</span>
                                    </button>
                                </div>

                                <div x-show="capturedImage" class="absolute bottom-3 inset-x-0 flex justify-center gap-2">
                                    <button type="button" @click="retakeSnapshot()" class="px-3 py-1.5 rounded-xl bg-stone-900/80 text-white text-xs font-bold hover:bg-stone-900 transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Ambil Ulang</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Opsional -->
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-[#786e60] mb-1">Catatan Kehadiran (Opsional)</label>
                            <input type="text" name="notes" placeholder="Misal: Bertugas di kasir depan / Gudang operasional"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-[#d6cfc2] bg-[#faf8f5] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                        </div>
                    </div>

                    <div class="p-4 bg-[#faf8f5] border-t border-[#f0ece1] flex items-center justify-between">
                        <div class="text-[11px] text-[#786e60]">
                            <span x-show="!capturedImage" class="text-amber-800 font-semibold">* Wajib mengambil foto selfie dari kamera</span>
                            <span x-show="capturedImage" class="text-emerald-700 font-bold">✓ Foto bukti siap disimpan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl border border-[#d6cfc2] text-xs font-semibold text-[#786e60] hover:bg-white">
                                Batal
                            </button>
                            <button type="submit"
                                :disabled="distanceMeters > 50 || !capturedImage"
                                class="px-5 py-2 rounded-xl bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Simpan Kehadiran
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview Foto -->
    <div x-show="previewPhotoUrl" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-stone-900/80 backdrop-blur-xs transition-opacity" @click="previewPhotoUrl = null"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-md w-full overflow-hidden p-3 shadow-2xl border border-[#e5dfd3]" @click.stop>
                <div class="flex justify-between items-center pb-2 border-b border-stone-200 mb-2">
                    <span class="text-xs font-bold text-[#262017]">Foto Bukti Kehadiran</span>
                    <button type="button" @click="previewPhotoUrl = null" class="text-stone-400 hover:text-stone-700 text-lg font-bold">&times;</button>
                </div>
                <img :src="previewPhotoUrl" class="w-full h-auto rounded-xl object-cover max-h-[70vh]">
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function attendanceManager() {
    return {
        officeLat: {{ $office['latitude'] }},
        officeLng: {{ $office['longitude'] }},
        userLat: {{ $office['latitude'] }},
        userLng: {{ $office['longitude'] }},
        distanceMeters: 0,
        geoStatus: 'ok',
        showModal: false,
        selectedType: '',
        selectedTypeName: '',
        cameraActive: false,
        capturedImage: null,
        previewPhotoUrl: null,
        stream: null,

        // Leaflet Elements
        map: null,
        officeMarker: null,
        userMarker: null,
        radiusCircle: null,
        distanceLine: null,

        init() {
            this.requestLocation();
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initMap();
                }, 200);
            });
        },

        initMap() {
            if (this.map || typeof L === 'undefined') return;
            const container = document.getElementById('attendance-map');
            if (!container) return;

            this.map = L.map('attendance-map', {
                scrollWheelZoom: false
            }).setView([this.officeLat, this.officeLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(this.map);

            // 1. Office Marker (Fira Cake - Ruko Ruby)
            const officeIcon = L.divIcon({
                className: 'custom-office-pin',
                html: `<div style="background-color:#c59b27; color:#12110e; border:2px solid #ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 3px 8px rgba(0,0,0,0.3); font-size:16px;">🏢</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            this.officeMarker = L.marker([this.officeLat, this.officeLng], { icon: officeIcon })
                .addTo(this.map)
                .bindPopup('<b>Kantor Fira Cake</b><br>BISOLPIN, Ruko Ruby, Jl. Boulevard Rubi II, Masale, Makassar');

            // 2. Lingkaran Radius 50 Meter
            this.radiusCircle = L.circle([this.officeLat, this.officeLng], {
                radius: 50,
                color: this.distanceMeters <= 50 ? '#10b981' : '#e11d48',
                fillColor: this.distanceMeters <= 50 ? '#10b981' : '#f43f5e',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(this.map);

            // 3. User Marker (Posisi Anda)
            const userIcon = L.divIcon({
                className: 'custom-user-pin',
                html: `<div style="background-color:#2563eb; color:#ffffff; border:2px solid #ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 3px 8px rgba(0,0,0,0.3); font-size:14px;">📍</div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            this.userMarker = L.marker([this.userLat, this.userLng], { icon: userIcon })
                .addTo(this.map)
                .bindPopup('<b>Posisi Anda Saat Ini</b><br>Jarak ke kantor: ' + this.distanceMeters.toFixed(1) + ' meter');

            // 4. Garis Jarak Dotted
            this.distanceLine = L.polyline([[this.officeLat, this.officeLng], [this.userLat, this.userLng]], {
                color: '#64748b',
                dashArray: '5, 5',
                weight: 2
            }).addTo(this.map);

            this.updateMapElements();
        },

        updateMapElements() {
            if (!this.map) return;

            if (this.userMarker) {
                this.userMarker.setLatLng([this.userLat, this.userLng]);
                this.userMarker.setPopupContent('<b>Posisi Anda Saat Ini</b><br>Jarak ke kantor: ' + this.distanceMeters.toFixed(1) + ' meter');
            }

            if (this.radiusCircle) {
                this.radiusCircle.setStyle({
                    color: this.distanceMeters <= 50 ? '#10b981' : '#e11d48',
                    fillColor: this.distanceMeters <= 50 ? '#10b981' : '#f43f5e',
                });
            }

            if (this.distanceLine) {
                this.distanceLine.setLatLngs([
                    [this.officeLat, this.officeLng],
                    [this.userLat, this.userLng]
                ]);
            }

            if (this.distanceMeters <= 50) {
                this.map.setView([this.officeLat, this.officeLng], 17);
            } else {
                const bounds = L.latLngBounds([
                    [this.officeLat, this.officeLng],
                    [this.userLat, this.userLng]
                ]);
                this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 18 });
            }
        },

        useOfficeLocation() {
            this.userLat = this.officeLat;
            this.userLng = this.officeLng;
            this.distanceMeters = 0;
            this.geoStatus = 'ok';
            this.syncHiddenCoordinates();
            this.updateMapElements();
        },

        syncHiddenCoordinates() {
            const inputLat = document.getElementById('attendanceInputLat');
            if (inputLat) inputLat.value = this.userLat;
            const inputLng = document.getElementById('attendanceInputLng');
            if (inputLng) inputLng.value = this.userLng;
        },

        requestLocation() {
            this.geoStatus = 'locating';
            if (!navigator.geolocation) {
                this.useOfficeLocation();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.userLat = pos.coords.latitude;
                    this.userLng = pos.coords.longitude;
                    this.distanceMeters = this.calculateDistance(
                        this.userLat, this.userLng,
                        this.officeLat, this.officeLng
                    );
                    this.geoStatus = 'ok';
                    this.syncHiddenCoordinates();
                    this.updateMapElements();
                },
                (err) => {
                    console.warn('Geolocation warning, menggunakan koordinat kantor default:', err);
                    this.useOfficeLocation();
                },
                { enableHighAccuracy: true, timeout: 6000 }
            );
        },

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const toRad = deg => (deg * Math.PI) / 180;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },

        openModal(type, typeName) {
            this.selectedType = type;
            this.selectedTypeName = typeName;
            this.capturedImage = null;
            this.showModal = true;

            const inputType = document.getElementById('attendanceInputType');
            if (inputType) inputType.value = type;
            this.syncHiddenCoordinates();

            this.startCamera();
        },

        closeModal() {
            this.showModal = false;
            this.stopCamera();
        },

        async startCamera() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                if (this.$refs.videoElement) {
                    this.$refs.videoElement.srcObject = this.stream;
                    this.cameraActive = true;
                }
            } catch (e) {
                console.warn('Kamera langsung tidak tersedia atau ditolak izin:', e);
                this.cameraActive = false;
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        takeSnapshot() {
            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            if (!video || !canvas) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            this.capturedImage = canvas.toDataURL('image/jpeg', 0.85);
            if (this.$refs.photoCameraInput) {
                this.$refs.photoCameraInput.value = this.capturedImage;
            }
            this.stopCamera();
        },

        retakeSnapshot() {
            this.capturedImage = null;
            if (this.$refs.photoCameraInput) {
                this.$refs.photoCameraInput.value = '';
            }
            this.startCamera();
        },

        openPhotoPreview(url) {
            this.previewPhotoUrl = url;
        }
    };
}
</script>
@endsection
