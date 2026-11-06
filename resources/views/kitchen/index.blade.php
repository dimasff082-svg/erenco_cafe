@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Kitchen Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-stone-900 via-slate-900 to-indigo-950 text-white p-7 sm:p-9 shadow-xl border border-indigo-500/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Kitchen Display System (KDS)
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Antrean Masakan Dapur</h2>
                <p class="text-slate-300 text-sm mt-1 max-w-xl">
                    Daftar pesanan masuk dari pelanggan meja. Ubah status pesanan saat mulai dimasak dan saat pesanan siap disajikan ke meja.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.location.reload()"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-lg transition transform hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Segarkan Antrean (Refresh)
                </button>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
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

    <!-- Tab Filter Status Dapur -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('kitchen.index', ['status' => 'active']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $statusFilter === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            <span>Antrean Aktif</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'active' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $counts['pending'] + $counts['processing'] }}
            </span>
        </a>

        <a href="{{ route('kitchen.index', ['status' => 'pending']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            <span>Menunggu (Pending)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $counts['pending'] }}
            </span>
        </a>

        <a href="{{ route('kitchen.index', ['status' => 'processing']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $statusFilter === 'processing' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            <span>Sedang Diproses (Cooking)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'processing' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                {{ $counts['processing'] }}
            </span>
        </a>

        <a href="{{ route('kitchen.index', ['status' => 'completed']) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            <span>Selesai Dimasak</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'completed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $counts['completed'] }}
            </span>
        </a>
    </div>

    <!-- Grid Kartu Pesanan Dapur -->
    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200/80 shadow-xs">
            <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <h4 class="text-base font-bold text-slate-700">Tidak ada pesanan pada filter ini</h4>
            <p class="text-xs text-slate-400 mt-1">Pesanan baru yang dikirim oleh pelanggan meja akan otomatis masuk ke antrean ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border-2 {{ $order->order_status === 'pending' ? 'border-amber-300 shadow-md ring-2 ring-amber-400/20' : ($order->order_status === 'processing' ? 'border-blue-400 shadow-md ring-2 ring-blue-400/20' : 'border-slate-200/80 shadow-xs') }} p-5 flex flex-col justify-between transition">
                    <div>
                        <!-- Header Kartu -->
                        <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                            <div>
                                @if($order->table_number === 'TAKEAWAY')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-purple-100 text-purple-900 border border-purple-300 shadow-2xs mb-1">
                                        <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        BAWA PULANG (TAKEAWAY)
                                    </span>
                                @elseif($order->table_number === 'ONLINE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-blue-100 text-blue-900 border border-blue-300 shadow-2xs mb-1">
                                        <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        PESANAN ONLINE
                                    </span>
                                @elseif($order->table_number === 'KIOSK')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-indigo-100 text-indigo-900 border border-indigo-300 shadow-2xs mb-1">
                                        KIOSK MANDIRI
                                    </span>
                                @else
                                    <span class="text-xl font-black text-slate-900 tracking-tight block">
                                        {{ $order->table_number ?? 'Meja M001' }}
                                    </span>
                                @endif
                                <span class="font-mono text-xs font-bold text-indigo-700 block">
                                    {{ $order->transaction_number }}
                                </span>
                            </div>

                            <div class="text-right">
                                @if($order->order_status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                        Menunggu
                                    </span>
                                @elseif($order->order_status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                        Sedang Dimasak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Selesai
                                    </span>
                                @endif
                                <span class="block text-[11px] text-slate-400 mt-1 font-medium">
                                    {{ $order->transaction_date->format('H:i') }} WIB ({{ $order->transaction_date->diffForHumans() }})
                                </span>
                            </div>
                        </div>

                        @if($order->customer_name || $order->customer_phone)
                            <div class="py-2 text-xs text-slate-600 font-semibold border-b border-slate-100 flex items-center justify-between">
                                <span>Pemesan: <strong class="text-slate-800">{{ $order->customer_name ?? 'Pelanggan' }}</strong></span>
                                @if($order->customer_phone)
                                    <span class="text-slate-500 font-mono text-[11px]">{{ $order->customer_phone }}</span>
                                @endif
                            </div>
                        @endif

                        @if($order->notes)
                            <div class="my-2 p-2.5 rounded-xl bg-amber-50 text-amber-900 text-xs border border-amber-200/60 font-medium">
                                <span class="font-bold block text-[10px] uppercase text-amber-800">Catatan Pesanan:</span>
                                {{ $order->notes }}
                            </div>
                        @endif

                        <!-- Daftar Item Menu -->
                        <div class="py-3 space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Daftar Menu:</span>
                            @foreach($order->items as $item)
                                <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2.5">
                                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                                            {{ $item->quantity }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-slate-800 text-xs block leading-tight">{{ $item->product_name_snapshot }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $item->sku_snapshot }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tombol Aksi Dapur -->
                    <div class="pt-4 border-t border-slate-100">
                        @if($order->order_status === 'pending')
                            <form action="{{ route('kitchen.orders.status', $order->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="order_status" value="processing">
                                <button type="submit"
                                    class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black shadow-md transition transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    </svg>
                                    Mulai Masak / Proses
                                </button>
                            </form>
                        @elseif($order->order_status === 'processing')
                            <form action="{{ route('kitchen.orders.status', $order->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="order_status" value="completed">
                                <button type="submit"
                                    class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md transition transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai Dimasak (Siap Saji)
                                </button>
                            </form>
                        @else
                            <div class="text-center py-2 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-xl">
                                &check; Pesanan Telah Disajikan
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
