@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 via-amber-700 to-stone-900 text-white p-7 sm:p-9 shadow-xl shadow-amber-950/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-md mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Terminal Kasir Cafe & Resto
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Daftar Pesanan Pelanggan</h2>
                <p class="text-amber-100/90 text-sm mt-1 max-w-xl">
                    Pelanggan memesan mandiri via QR Code meja. Tugas Kasir adalah memverifikasi dan mengonfirmasi pembayaran pesanan atau tagihan Open Bill.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Tombol Disable / Enable Suara Notifikasi Orderan Masuk -->
                <button type="button" id="btn-toggle-order-sound" onclick="window.toggleOrderSound()"
                    title="Nyalakan / Matikan Suara Notifikasi Pesanan Masuk"
                    class="inline-flex items-center gap-2 bg-stone-900/60 hover:bg-stone-900/80 text-emerald-300 border border-emerald-500/50 text-xs font-bold px-4 py-3 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95">
                    <span id="sound-toggle-icon">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </span>
                    <span id="sound-toggle-label">Suara: Aktif</span>
                </button>

                <button type="button" onclick="window.playOrderAlertSound('Halo ada orderan masuk', true)"
                    title="Tes Notifikasi Suara: Halo ada orderan masuk"
                    class="inline-flex items-center gap-2 bg-stone-900/40 hover:bg-stone-900/60 text-amber-200 border border-amber-300/40 text-xs font-bold px-4 py-3 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    Tes Suara Orderan
                </button>
                <a href="{{ route('cashier.open_bills.index') }}" class="inline-flex items-center gap-2 bg-white text-stone-900 hover:bg-amber-50 text-xs font-bold px-4 py-3 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Kelola Open Bill Meja
                    @if($activeOpenBillsCount > 0)
                        <span class="bg-amber-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $activeOpenBillsCount }}</span>
                    @endif
                </a>
            </div>
        </div>
        <!-- Decorative Glow -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Permintaan Buka Open Bill dari Pelanggan -->
    @if(isset($pendingOpenBillRequests) && $pendingOpenBillRequests->isNotEmpty())
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-400 rounded-3xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                    <h3 class="font-bold text-sm text-amber-950 font-serif-classic">
                        Permintaan Buka Open Bill Masuk ({{ $pendingOpenBillRequests->count() }} Meja Menunggu Konfirmasi)
                    </h3>
                </div>
                <span class="text-[11px] font-bold text-amber-800 bg-amber-200/60 px-2.5 py-0.5 rounded-full">Tindakan Kasir</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($pendingOpenBillRequests as $req)
                    <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-2xs flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-amber-900 bg-amber-100 px-2 py-0.5 rounded-lg">{{ $req->table_number }}</span>
                                <span class="text-[10px] text-slate-400">{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 mt-2">{{ $req->table->name ?? 'Meja ' . $req->table_number }}</h4>
                            @if($req->customer_name)
                                <p class="text-xs text-slate-600 mt-0.5">Pemohon: <strong>{{ $req->customer_name }}</strong></p>
                            @endif
                            @if($req->notes)
                                <p class="text-[11px] text-slate-500 italic mt-1">"{{ $req->notes }}"</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                            <form action="{{ route('cashier.open_bills.approve', $req->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Setujui
                                </button>
                            </form>
                            <form action="{{ route('cashier.open_bills.reject', $req->id) }}" method="POST"
                                data-confirm="Apakah Anda yakin ingin menolak permintaan Open Bill untuk Meja {{ $req->restaurantTable?->table_number ?? '-' }}?"
                                data-confirm-title="Tolak Open Bill"
                                data-confirm-btn="Ya, Tolak Permintaan"
                                data-confirm-type="danger">
                                @csrf
                                <button type="submit" class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Kartu Status Ringkasan Kasir -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Belum Dibayar (Hari Ini)</span>
                <div class="text-2xl font-black text-rose-600 tracking-tight mt-1">
                    Rp {{ number_format($unpaidTotal, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $unpaidCount }} pesanan menanti konfirmasi</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Sudah Dibayar (Lunas)</span>
                <div class="text-2xl font-black text-emerald-600 tracking-tight mt-1">
                    Rp {{ number_format($paidTotal, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $paidCount }} pesanan telah selesai</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Open Bill Aktif</span>
                <div class="text-2xl font-black text-amber-600 tracking-tight mt-1">
                    {{ $activeOpenBillsCount }} <span class="text-sm font-normal text-slate-400">meja</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Tagihan sesi meja digabungkan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">QR Meja Pelanggan</span>
                <div class="text-2xl font-black text-indigo-600 tracking-tight mt-1">
                    {{ $tables->count() }} <span class="text-sm font-normal text-slate-400">meja</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Link pemesanan mandiri aktif</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Pesanan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form action="{{ route('cashier.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari ID Pesanan (ORD...) atau Nama..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="payment_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700">
                    <option value="all">Semua Status Pembayaran</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="table_number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700">
                    <option value="all">Semua Meja</option>
                    @foreach($tables as $t)
                        <option value="{{ $t->table_number }}" {{ request('table_number') === $t->table_number ? 'selected' : '' }}>{{ $t->table_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1.5">
                <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-3 py-2 rounded-xl transition shadow-sm">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'payment_status', 'table_number']))
                    <a href="{{ route('cashier.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL UTAMA DASHBOARD KASIR SESUAI SPESIFIKASI REVISI -->
    <!-- Kolom: ID Pesanan | Meja | Total | Pembayaran | Aksi -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Tabel Pesanan Masuk Pelanggan</h3>
                <p class="text-xs text-slate-500">Konfirmasi pembayaran tunai atau pantau pesanan online & meja yang masuk.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Tombol Salin Link Pesan Online untuk Pelanggan -->
                <button type="button" onclick="copyOnlineOrderLink()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 transition shadow-2xs"
                    title="Salin link pemesanan online untuk dikirimkan ke pelanggan">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span id="copyLinkText">Salin Link Pesan Online</span>
                </button>

                <!-- Tombol Kirim Link via WA -->
                <button type="button" onclick="openShareOnlineModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs"
                    title="Kirim link pemesanan online ke WhatsApp pelanggan">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.143.038.196-.023.053-.06.226-.263.286-.353.06-.09.12-.075.203-.045.083.03.526.248.616.293.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                    </svg>
                    <span>Kirim Link via WA</span>
                </button>

                <div class="text-xs text-slate-400 pl-2 border-l border-slate-200">
                    Total: <span class="font-bold text-slate-700">{{ $sales->total() }}</span> pesanan
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6">ID Pesanan</th>
                        <th class="py-3.5 px-6">Meja</th>
                        <th class="py-3.5 px-6 text-right">Total</th>
                        <th class="py-3.5 px-6 text-center">Pembayaran</th>
                        <th class="py-3.5 px-6 text-center">Status Dapur</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- ID Pesanan (misal: ORD001, ONL001) -->
                            <td class="py-4 px-6 font-mono text-xs font-extrabold text-amber-800">
                                <div>{{ $sale->transaction_number }}</div>
                                <div class="text-[11px] font-normal font-sans text-slate-400 mt-0.5">
                                    {{ $sale->transaction_date->format('H:i') }} WIB
                                    @if($sale->customer_name)
                                        &bull; <span class="text-slate-600 font-medium">{{ $sale->customer_name }}</span>
                                    @endif
                                </div>
                                @if($sale->customer_phone)
                                    <div class="text-[10px] font-sans text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span>{{ $sale->customer_phone }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Meja / Tipe Pesanan (Online, Takeaway, Kiosk, Meja) -->
                            <td class="py-4 px-6">
                                @if($sale->table_number === 'ONLINE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-blue-50 text-blue-700 border border-blue-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        PESANAN ONLINE
                                    </span>
                                @elseif($sale->table_number === 'TAKEAWAY')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-purple-50 text-purple-700 border border-purple-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        TAKEAWAY
                                    </span>
                                @elseif($sale->table_number === 'KIOSK')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        KIOSK MANDIRI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-extrabold bg-stone-100 text-stone-800 border border-stone-300">
                                        <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $sale->table_number ?? 'M001' }}
                                    </span>
                                @endif
                                @if($sale->open_bill_id)
                                    <span class="block text-[10px] font-bold text-amber-600 mt-1">Open Bill #{{ $sale->openBill?->bill_number }}</span>
                                @endif
                            </td>

                            <!-- Total (misal: Rp45.000) -->
                            <td class="py-4 px-6 text-right font-black text-slate-900 text-sm">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                <span class="block text-[11px] font-normal text-slate-400">{{ $sale->items->sum('quantity') }} menu</span>
                            </td>

                            <!-- Pembayaran (Belum Dibayar / Lunas) -->
                            <td class="py-4 px-6 text-center">
                                @if($sale->isPaid())
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Lunas
                                    </span>
                                    @if($sale->payment)
                                        <span class="block text-[10px] font-semibold text-emerald-700 mt-0.5">{{ $sale->payment->methodLabel() }}</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum Dibayar
                                    </span>
                                @endif
                            </td>

                            <!-- Status Dapur (Informatif untuk Kasir: Menunggu, Diproses, Selesai) -->
                            <td class="py-4 px-6 text-center">
                                @if($sale->order_status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                                        Selesai
                                    </span>
                                @elseif($sale->order_status === 'processing')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700">
                                        Diproses
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700">
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    <!-- Tombol Aksi WhatsApp Khusus Pesanan Online Saja (Takeaway tidak ada tombol WA) -->
                                    @if($sale->table_number === 'ONLINE' && $sale->customer_phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone);
                                            if (str_starts_with($cleanPhone, '0')) {
                                                $waPhone = '62' . substr($cleanPhone, 1);
                                            } elseif (!str_starts_with($cleanPhone, '62')) {
                                                $waPhone = '62' . $cleanPhone;
                                            } else {
                                                $waPhone = $cleanPhone;
                                            }
                                            $itemsList = $sale->items->map(fn($it) => "- {$it->quantity}x {$it->product_name_snapshot}")->implode("\n");
                                            $waMsg = "Halo Kak {$sale->customer_name}, terima kasih telah memesan di {$storeName}!\n\nPesanan Online Anda ({$sale->transaction_number}) senilai Rp " . number_format($sale->total_amount, 0, ',', '.') . " telah kami terima dan LUNAS via Payment Gateway.\n\nRincian Pesanan:\n{$itemsList}\n\nSaat ini pesanan sedang dipersiapkan fresh oleh dapur kami. Kami akan segera menginfokan kembali saat pesanan siap diantar / diambil. Terima kasih!";
                                            $waHref = "https://api.whatsapp.com/send?phone={$waPhone}&text=" . urlencode($waMsg);
                                        @endphp
                                        <a href="{{ $waHref }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold shadow-xs transition"
                                            title="Chat WhatsApp Pelanggan ({{ $sale->customer_phone }})">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.143.038.196-.023.053-.06.226-.263.286-.353.06-.09.12-.075.203-.045.083.03.526.248.616.293.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                                            </svg>
                                            <span>Chat WA</span>
                                        </a>
                                    @endif

                                    @if($sale->isUnpaid())
                                        <!-- Tombol Konfirmasi Pembayaran -->
                                        <button type="button"
                                            onclick="openConfirmModal('{{ $sale->id }}', '{{ $sale->transaction_number }}', '{{ $sale->table_number ?? 'M001' }}', '{{ (float)$sale->total_amount }}')"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm transition transform hover:scale-105 active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Konfirmasi
                                        </button>
                                    @else
                                        <!-- Tombol Detail / Struk untuk pesanan Lunas -->
                                        <button type="button"
                                            onclick="openDetailModal('{{ $sale->id }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                            Detail
                                        </button>
                                        <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition" title="Cetak Struk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Belum ada pesanan masuk.</p>
                                <p class="text-xs text-slate-400 mt-1">Pelanggan dapat melakukan pemesanan makanan melalui link QR Code meja.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>


</div>

<!-- MODAL KONFIRMASI PEMBAYARAN KASIR -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 transform transition-all">
        <form id="confirmForm" method="POST" action="">
            @csrf
            <div class="p-6 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-200">Konfirmasi Pembayaran Kasir</span>
                    <h3 id="modalOrderNumber" class="text-xl font-black mt-0.5">ORD001</h3>
                </div>
                <div id="modalTableNumber" class="px-3 py-1 rounded-xl bg-white/20 text-white font-extrabold text-sm backdrop-blur-md">
                    Meja M001
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Tagihan</label>
                    <div id="modalTotalFormatted" class="text-3xl font-black text-slate-900">
                        Rp 0
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="cash" checked class="peer sr-only" onchange="toggleTenderedInput(true)">
                            <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 transition">
                                Tunai
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="qris" class="peer sr-only" onchange="toggleTenderedInput(false)">
                            <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 transition">
                                QRIS
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="transfer" class="peer sr-only" onchange="toggleTenderedInput(false)">
                            <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 transition">
                                Transfer
                            </div>
                        </label>
                    </div>
                </div>

                <div id="tenderedWrapper" class="space-y-2">
                    <label class="block text-xs font-bold text-slate-600">Jumlah Uang Diterima (Rp) *</label>
                    <input type="text" id="inputTendered" inputmode="numeric"
                        placeholder="Ketik nominal uang dari pelanggan (contoh: 60.000)..."
                        oninput="handleTenderedInput(this)"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-base font-bold text-slate-900 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <input type="hidden" id="rawAmountTendered" name="amount_tendered" value="">

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-500 font-medium">Uang Kembalian:</span>
                        <span id="labelChange" class="font-black text-emerald-600 text-sm">Rp 0</span>
                    </div>

                    <div class="pt-1.5 space-y-2">
                        <button type="button" onclick="setExactAmount()" class="w-full py-2 px-3 rounded-xl bg-amber-100 hover:bg-amber-200 text-xs font-black text-amber-900 border border-amber-300 transition flex items-center justify-center gap-2 shadow-2xs">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Uang Pas: <span id="labelExactAmount">Rp 0</span></span>
                        </button>
                        
                        <div class="grid grid-cols-4 gap-1.5 text-[11px] font-bold text-slate-700">
                            <button type="button" onclick="setPresetAmount(10000)" class="py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">10.000</button>
                            <button type="button" onclick="setPresetAmount(20000)" class="py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">20.000</button>
                            <button type="button" onclick="setPresetAmount(50000)" class="py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">50.000</button>
                            <button type="button" onclick="setPresetAmount(100000)" class="py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">100.000</button>
                        </div>
                    </div>
                </div>

                <div class="pt-2 text-xs text-slate-400">
                    Sistem akan mencatat ID Kasir Anda ({{ auth()->user()->name }}) dan waktu konfirmasi pembayaran ini secara permanen.
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeConfirmModal()" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-200 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-extrabold shadow-md transition transform hover:scale-105 active:scale-95">
                    Konfirmasi Pembayaran Lunas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL PESANAN KASIR -->
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-100">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Menu Pesanan</span>
                <h3 id="detailOrderTitle" class="text-lg font-black text-slate-900">Detail Pesanan</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4" id="detailModalContent">
            <!-- Dynamic Content populated via JS -->
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-800 text-xs font-bold hover:bg-slate-300 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL BAGIKAN LINK PESAN ONLINE KE WHATSAPP PELANGGAN -->
<div id="shareOnlineModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.143.038.196-.023.053-.06.226-.263.286-.353.06-.09.12-.075.203-.045.083.03.526.248.616.293.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Kirim Link Pesan Online</h3>
                    <p class="text-xs text-slate-500">Kirim tautan menu online ke WhatsApp pelanggan.</p>
                </div>
            </div>
            <button type="button" onclick="closeShareOnlineModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Pelanggan *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2.5 text-xs font-bold text-slate-400">+62</span>
                    <input type="tel" id="shareWaPhoneInput" placeholder="81234567890"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 py-2.5 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Masukkan nomor handphone pelanggan yang sedang chat/telepon.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Link Pemesanan Online</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="shareOnlineLinkInput" readonly value="{{ url('/pesan-online') }}"
                        class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono text-slate-700 select-all">
                    <button type="button" onclick="copyOnlineOrderLink()"
                        class="shrink-0 px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition">
                        Salin
                    </button>
                </div>
            </div>

            <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl text-[11px] text-emerald-950 space-y-1">
                <span class="font-bold block text-emerald-900">Format Pesan Otomatis:</span>
                <p class="italic">"Halo! Silakan klik link berikut untuk melihat menu dan memesan langsung di {{ $storeName }}: {{ url('/pesan-online') }} Terima kasih!"</p>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button" onclick="closeShareOnlineModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 text-xs font-bold transition">
                Batal
            </button>
            <button type="button" onclick="sendOnlineLinkToWa()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.143.038.196-.023.053-.06.226-.263.286-.353.06-.09.12-.075.203-.045.083.03.526.248.616.293.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                </svg>
                Buka & Kirim WhatsApp
            </button>
        </div>
    </div>
</div>

<script>
    let currentOrderTotal = 0;

    // Cache of sales for Detail Modal
    const salesData = @json($sales->items());

    function getCleanNumericValue(str) {
        if (!str) return 0;
        const cleaned = String(str).replace(/[^\d]/g, '');
        return cleaned ? parseInt(cleaned, 10) : 0;
    }

    function handleTenderedInput(inputEl) {
        const raw = getCleanNumericValue(inputEl.value);
        if (raw > 0) {
            inputEl.value = Number(raw).toLocaleString('id-ID');
            document.getElementById('rawAmountTendered').value = raw;
        } else {
            inputEl.value = '';
            document.getElementById('rawAmountTendered').value = '';
        }
        calculateChange();
    }

    function openConfirmModal(saleId, orderNumber, tableNumber, totalAmount) {
        currentOrderTotal = parseFloat(totalAmount);
        document.getElementById('confirmForm').action = "/cashier/orders/" + saleId + "/confirm-payment";
        document.getElementById('modalOrderNumber').innerText = orderNumber;
        document.getElementById('modalTableNumber').innerText = "Meja " + tableNumber;
        document.getElementById('modalTotalFormatted').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(currentOrderTotal);
        document.getElementById('labelExactAmount').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(currentOrderTotal);
        
        // Kolom jumlah uang yang diterima dikosongkan secara default sesuai instruksi user
        document.getElementById('inputTendered').value = '';
        document.getElementById('rawAmountTendered').value = '';
        document.getElementById('labelChange').innerText = "Rp 0";

        document.getElementById('confirmModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('inputTendered').focus();
        }, 100);
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    function toggleTenderedInput(isCash) {
        const wrapper = document.getElementById('tenderedWrapper');
        if (isCash) {
            wrapper.classList.remove('hidden');
            document.getElementById('inputTendered').value = '';
            document.getElementById('rawAmountTendered').value = '';
            document.getElementById('labelChange').innerText = "Rp 0";
        } else {
            wrapper.classList.add('hidden');
            document.getElementById('inputTendered').value = Number(currentOrderTotal).toLocaleString('id-ID');
            document.getElementById('rawAmountTendered').value = currentOrderTotal;
        }
    }

    function setExactAmount() {
        document.getElementById('inputTendered').value = Number(currentOrderTotal).toLocaleString('id-ID');
        document.getElementById('rawAmountTendered').value = currentOrderTotal;
        calculateChange();
    }

    function setPresetAmount(amount) {
        document.getElementById('inputTendered').value = Number(amount).toLocaleString('id-ID');
        document.getElementById('rawAmountTendered').value = amount;
        calculateChange();
    }

    function calculateChange() {
        const tendered = getCleanNumericValue(document.getElementById('inputTendered').value);
        const change = Math.max(0, tendered - currentOrderTotal);
        document.getElementById('labelChange').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(change);
    }

    function openDetailModal(saleId) {
        const sale = salesData.find(s => s.id == saleId);
        if (!sale) return;

        let tableText = 'Meja ' + (sale.table_number || 'M001');
        if (sale.table_number === 'ONLINE') {
            tableText = 'Pesanan Online';
        } else if (sale.table_number === 'TAKEAWAY') {
            tableText = 'Bawa Pulang (Takeaway)';
        } else if (sale.table_number === 'KIOSK') {
            tableText = 'Kiosk Mandiri';
        }

        document.getElementById('detailOrderTitle').innerText = sale.transaction_number + " (" + tableText + ")";
        
        let customerInfo = '';
        if (sale.customer_name || sale.customer_phone) {
            customerInfo = `
                <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-3 text-xs space-y-1 mb-3">
                    <div class="flex justify-between">
                        <span class="text-blue-900 font-semibold">Nama Pemesan:</span>
                        <span class="font-bold text-blue-950">${sale.customer_name || '-'}</span>
                    </div>
                    ${sale.customer_phone ? `
                    <div class="flex justify-between items-center">
                        <span class="text-blue-900 font-semibold">WhatsApp / HP:</span>
                        <div class="flex items-center gap-1.5 font-bold text-blue-950">
                            <span>${sale.customer_phone}</span>
                            ${sale.table_number === 'ONLINE' ? `
                            <a href="https://api.whatsapp.com/send?phone=${sale.customer_phone.replace(/^0/, '62').replace(/\D/g, '')}" target="_blank"
                                class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-600 text-white text-[10px] font-bold hover:bg-emerald-700 transition">
                                Chat WA
                            </a>
                            </a>` : ''}
                        </div>
                    </div>` : ''}
                    ${sale.notes ? `
                    <div class="pt-1.5 border-t border-blue-200/60 text-[11px] text-blue-900">
                        <span class="font-semibold block text-slate-500">Catatan / Alamat:</span>
                        <p class="font-medium text-slate-800 italic">${sale.notes}</p>
                    </div>` : ''}
                </div>
            `;
        }

        let itemsHtml = '<div class="divide-y divide-slate-100">';
        if (sale.items && sale.items.length > 0) {
            sale.items.forEach(item => {
                itemsHtml += `
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-800">${item.quantity}x ${item.product_name_snapshot}</span>
                            <span class="text-slate-400 block text-[11px]">@ Rp ${new Intl.NumberFormat('id-ID').format(item.unit_price)}</span>
                        </div>
                        <span class="font-black text-slate-900">Rp ${new Intl.NumberFormat('id-ID').format(item.line_total)}</span>
                    </div>
                `;
            });
        }
        itemsHtml += '</div>';

        let infoHtml = `
            <div class="bg-slate-50 p-3.5 rounded-2xl space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Pembayaran:</span>
                    <span class="font-bold ${sale.payment_status === 'paid' ? 'text-emerald-700' : 'text-rose-700'}">${sale.payment_status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR'}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Dapur:</span>
                    <span class="font-bold text-slate-800">${sale.order_status === 'completed' ? 'Selesai' : (sale.order_status === 'processing' ? 'Diproses' : 'Menunggu')}</span>
                </div>
                ${sale.confirmed_at ? `
                <div class="flex justify-between">
                    <span class="text-slate-500">Dikonfirmasi Oleh:</span>
                    <span class="font-semibold text-slate-700">${sale.confirmer ? sale.confirmer.name : 'Kasir'}</span>
                </div>` : ''}
            </div>
        `;

        let totalHtml = `
            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                <span class="font-bold text-slate-600 text-xs uppercase">Total Tagihan:</span>
                <span class="text-xl font-black text-slate-900">Rp ${new Intl.NumberFormat('id-ID').format(sale.total_amount)}</span>
            </div>
        `;

        document.getElementById('detailModalContent').innerHTML = customerInfo + itemsHtml + infoHtml + totalHtml;
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    const onlineOrderUrl = "{{ url('/pesan-online') }}";

    function copyOnlineOrderLink() {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(onlineOrderUrl).then(() => {
                showCopySuccess();
            }).catch(() => {
                fallbackCopyText(onlineOrderUrl);
            });
        } else {
            fallbackCopyText(onlineOrderUrl);
        }
    }

    function fallbackCopyText(text) {
        const tempInput = document.createElement('input');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (e) {
            prompt('Salin link pemesanan online berikut:', text);
        }
        document.body.removeChild(tempInput);
    }

    function showCopySuccess() {
        const btnText = document.getElementById('copyLinkText');
        if (btnText) {
            const originalText = btnText.innerText;
            btnText.innerText = 'Link Tersalin!';
            setTimeout(() => {
                btnText.innerText = originalText;
            }, 2000);
        }
    }

    function openShareOnlineModal() {
        document.getElementById('shareOnlineModal').classList.remove('hidden');
        setTimeout(() => {
            const input = document.getElementById('shareWaPhoneInput');
            if (input) input.focus();
        }, 100);
    }

    function closeShareOnlineModal() {
        document.getElementById('shareOnlineModal').classList.add('hidden');
    }

    function sendOnlineLinkToWa() {
        const input = document.getElementById('shareWaPhoneInput');
        let phone = (input ? input.value : '').replace(/\D/g, '');
        if (!phone) {
            alert('Mohon masukkan nomor WhatsApp pelanggan terlebih dahulu.');
            if (input) input.focus();
            return;
        }
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        } else if (!phone.startsWith('62')) {
            phone = '62' + phone;
        }

        const msg = `Halo! Silakan klik link berikut untuk melihat menu dan memesan langsung di ${@js($storeName)}:\n\n${onlineOrderUrl}\n\nPembayaran dapat dilakukan langsung via QRIS/Payment Gateway. Terima kasih!`;
        const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`;
        window.open(waUrl, '_blank');
        closeShareOnlineModal();
    }
</script>
@endsection
