<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Menu - Meja {{ $table->table_number }} | Cafe & Resto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,800;1,600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-resto { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased pb-28">

    <!-- Top Sticky Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 shadow-2xs">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-10 h-10 object-contain drop-shadow-sm shrink-0" onerror="this.src='{{ asset('images/default-cake.png') }}'">
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 leading-tight">
                        <span>{{ $storeNameLine1 }}</span>
                        @if(!empty($storeNameLine2))
                            <span class="block text-xs font-semibold text-[#c59b27]">{{ $storeNameLine2 }}</span>
                        @endif
                    </h1>
                    <p class="text-[10px] text-slate-400">Pemesanan Mandiri Pelanggan</p>
                </div>
            </div>

            <!-- Table Badge -->
            <div class="flex items-center gap-2">
                @if($activeOpenBill)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl font-bold text-xs bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Open Bill: {{ $table->table_number }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl font-black text-xs bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $table->table_number }}
                    </span>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-2xl mx-auto w-full p-4 space-y-5">
        <!-- Hero Welcome -->
        <div class="rounded-3xl bg-gradient-to-br from-stone-900 via-amber-950 to-stone-900 text-white p-6 shadow-xl relative overflow-hidden border border-amber-500/20">
            <div class="relative z-10">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300">Selamat Datang di Meja Anda</span>
                <h2 class="text-2xl font-black mt-1 font-serif-resto">{{ $table->name }}</h2>
                <p class="text-xs text-stone-300 mt-1 leading-relaxed">
                    Pilih menu makanan & minuman favorit Anda di bawah ini, lalu klik kirim pesanan untuk diproses langsung oleh Dapur.
                </p>

                @if($activeOpenBill)
                    <div class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Open Bill Meja Aktif &bull; Pesan bebas, bayar nanti sekaligus</span>
                    </div>
                @elseif($requestedOpenBill)
                    <div class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 rounded-xl bg-amber-500/20 border border-amber-400/40 text-amber-300 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Permintaan Open Bill Sedang Menunggu Kasir</span>
                    </div>
                @else
                    <!-- Tombol Request Open Bill untuk Pelanggan -->
                    <div class="mt-4 pt-3 border-t border-amber-500/20 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[11px] text-stone-300">Ingin pesan berkali-kali tanpa harus bayar per pesanan?</p>
                        <button type="button" onclick="openRequestOpenBillModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-stone-950 font-extrabold text-xs shadow-md transition transform hover:scale-105 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Minta Buka Open Bill ke Kasir
                        </button>
                    </div>
                @endif
            </div>
            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl text-xs flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-2xl text-xs flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-xs shadow-2xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner Status Permintaan Open Bill Menunggu Kasir -->
        @if($requestedOpenBill && ! $activeOpenBill)
            <div class="rounded-3xl bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300 p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-300 flex items-center justify-center shrink-0">
                            <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-amber-950">Permintaan Open Bill Sedang Menunggu Kasir</h4>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Kasir sedang meninjau permintaan Anda untuk meja ini. Setelah disetujui, Anda dapat memesan menu tanpa perlu membayar di awal.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-amber-200 text-xs">
                    <button type="button" onclick="window.location.reload()" class="font-bold text-amber-900 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Segarkan Status (Refresh)
                    </button>
                    <form action="{{ route('order.cancel_open_bill_request', $table->table_number) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold hover:underline">
                            Batalkan Permintaan
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- NOTA TAGIHAN BERJALAN MEJA (LIVE RUNNING BILL) -->
        @if($activeOpenBill)
            <div id="runningBillCard" class="bg-white rounded-3xl border-2 border-amber-300/90 shadow-md p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Nota Tagihan Meja (Open Bill)</span>
                            <span class="text-[10px] font-mono font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded-lg border border-amber-300">{{ $activeOpenBill->bill_number }}</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mt-1 font-serif-resto">Rincian Seluruh Pesanan {{ $table->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if($activeOpenBill->customer_name)
                                Pemesan: <strong class="text-slate-800">{{ $activeOpenBill->customer_name }}</strong> &bull;
                            @endif
                            Dibuka sejak: {{ $activeOpenBill->opened_at?->format('H:i') }} WIB
                        </p>
                    </div>

                    <div class="text-left sm:text-right bg-amber-50 sm:bg-transparent p-3 sm:p-0 rounded-2xl border sm:border-0 border-amber-200">
                        <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Tagihan Akumulasi</span>
                        <span class="text-2xl font-black text-amber-950 font-serif-resto">
                            Rp {{ number_format($activeOpenBill->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Daftar Pesanan di Meja ini -->
                @if($activeOpenBill->sales->isEmpty())
                    <div class="text-center py-6 text-slate-400 text-xs bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="font-bold text-slate-600">Belum ada menu yang dikirim ke Dapur.</p>
                        <p class="text-[11px] mt-0.5">Pilih menu favorit Anda di bawah lalu kirim pesanan (tidak perlu bayar di awal).</p>
                    </div>
                @else
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @foreach($activeOpenBill->sales as $sale)
                            <div class="bg-[#faf8f4] rounded-2xl p-3.5 border border-[#eee5d3] text-xs space-y-2">
                                <div class="flex items-center justify-between border-b border-[#e9ded0] pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-extrabold text-[#8a681c]">{{ $sale->transaction_number }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $sale->transaction_date->format('H:i') }} WIB</span>
                                    </div>
                                    <div>
                                        @if($sale->order_status === 'completed')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Siap Saji
                                            </span>
                                        @elseif($sale->order_status === 'processing')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-full border border-sky-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                                Sedang Dimasak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu Dapur
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    @foreach($sale->items as $item)
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-slate-800">
                                                <strong class="text-slate-900">{{ $item->quantity }}x</strong>
                                                {{ $item->product_name_snapshot }}
                                            </span>
                                            <span class="font-bold text-slate-900">Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Dua Pilihan Utama: Masih Ingin Memesan vs Sudah Mau Membayar -->
                <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5">
                    <button type="button" onclick="scrollToMenu()"
                        class="flex-1 py-3 px-4 rounded-2xl bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold transition flex items-center justify-center gap-2 border border-amber-300 shadow-2xs">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Masih Ingin Memesan (Pilih Menu)</span>
                    </button>

                    @if($activeOpenBill->total_amount > 0)
                        <button type="button" onclick="openSettlementModal()"
                            class="flex-1 py-3 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg shadow-emerald-600/25 transition transform hover:scale-102 active:scale-98 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Sudah Mau Membayar &rarr;</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Menu Section Header & Search Bar -->
        <div id="menuCatalogSection" class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 text-base font-serif-resto">Katalog Menu Cafe & Resto</h3>
                <span class="text-xs text-slate-400">Pilih menu favorit</span>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input type="text" id="menuSearch" oninput="filterMenu()"
                    placeholder="Cari makanan atau minuman..."
                    class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 pl-10 text-xs font-medium text-slate-800 shadow-2xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Category Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button type="button" onclick="filterCategory('all')" id="catTab-all"
                    class="cat-tab px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-amber-600 text-white shadow-xs transition">
                    Semua Menu
                </button>
                @foreach($categories as $category)
                    <button type="button" onclick="filterCategory('cat-{{ $category->id }}')" id="catTab-cat-{{ $category->id }}"
                        class="cat-tab px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 shadow-xs transition">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Menu Grid by Categories -->
        <div class="space-y-6">
            @foreach($categories as $category)
                <div class="category-section" id="cat-{{ $category->id }}">
                    <h3 class="font-bold text-slate-900 text-sm mb-3 flex items-center justify-between">
                        <span>{{ $category->name }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ $category->products->count() }} menu</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($category->products as $product)
                            <div class="menu-item bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-2xs hover:shadow-md transition flex gap-3.5"
                                data-name="{{ strtolower($product->name) }}" data-category="cat-{{ $category->id }}">
                                
                                <!-- Foto Menu -->
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden shrink-0 bg-amber-50/70 border border-slate-200/80 flex items-center justify-center">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-amber-700/60 flex flex-col items-center justify-center p-2 text-center">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Konten Menu & Aksi -->
                                <div class="flex-1 flex flex-col justify-between min-w-0">
                                    <div>
                                        <div class="flex items-start justify-between gap-1">
                                            <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-1">{{ $product->name }}</h4>
                                            <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ $product->sku }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                            <span class="inline-block text-[9px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded font-medium border border-amber-200/60">
                                                {{ $category->name }}
                                            </span>
                                        </div>
                                        @if($product->description)
                                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                                        @endif
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                        <span class="font-black text-amber-900 text-sm">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </span>

                                        <!-- Controller Tambah / Kurangi Menu -->
                                        <div id="btn-group-{{ $product->id }}">
                                            <button type="button" id="btn-add-{{ $product->id }}"
                                                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition transform hover:scale-105 active:scale-95">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Tambah
                                            </button>
                                            <div id="counter-{{ $product->id }}" class="hidden items-center gap-1.5 bg-amber-50 border border-amber-300 rounded-xl p-1 shadow-2xs">
                                                <button type="button" onclick="decreaseQty({{ $product->id }})"
                                                    class="w-6 h-6 rounded-lg bg-white text-amber-900 border border-amber-300 flex items-center justify-center font-bold text-xs hover:bg-amber-100">
                                                    -
                                                </button>
                                                <span id="qty-{{ $product->id }}" class="font-black text-xs text-amber-950 px-1.5 min-w-[1.25rem] text-center">
                                                    0
                                                </span>
                                                <button type="button" onclick="increaseQty({{ $product->id }})"
                                                    class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-xs hover:bg-amber-700">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Bottom Sticky Floating Cart Bar -->
    <div id="floatingCart" class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 p-3.5 shadow-xl hidden">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center font-extrabold text-sm relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cartCountBadge" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center shadow-xs">0</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 uppercase font-bold block">
                        {{ $activeOpenBill ? 'Pesanan Tambahan' : 'Total Belanja' }}
                    </span>
                    <span id="cartTotalText" class="text-lg font-black text-slate-900">Rp 0</span>
                </div>
            </div>

            <button type="button" onclick="openCartModal()"
                class="px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white text-xs font-black shadow-lg shadow-amber-600/25 transition transform hover:scale-105 active:scale-95 flex items-center gap-2">
                <span>{{ $activeOpenBill ? 'Kirim Pesanan Tambahan' : 'Lihat Pesanan & Kirim' }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Modal Keranjang & Checkout Pelanggan -->
    <div id="cartModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-100">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Meja {{ $table->table_number }}</span>
                    <h3 class="text-lg font-black text-slate-900">
                        {{ $activeOpenBill ? 'Kirim Pesanan Tambahan' : 'Konfirmasi Pesanan Anda' }}
                    </h3>
                </div>
                <button type="button" onclick="closeCartModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Pesanan Pelanggan -->
            <form id="checkoutForm" action="{{ route('order.checkout', $table->table_number) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="p-5 overflow-y-auto space-y-4 flex-1">
                    <!-- Selected Items List -->
                    <div id="cartItemsList" class="divide-y divide-slate-100">
                        <!-- Populated via JavaScript -->
                    </div>

                    <!-- Customer Info Input -->
                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        @if(! $activeOpenBill)
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemesan (Opsional)</label>
                                <input type="text" name="customer_name" placeholder="Misal: Dimas / Kakak Meja 1..."
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500">
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan untuk Dapur (Opsional)</label>
                            <textarea name="notes" rows="2" placeholder="Misal: Es dipisah, sambal banyakin, dll..."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>
                    </div>

                    @if($activeOpenBill)
                        <!-- Jika Open Bill Aktif: Langsung Gabung Tanpa Bayar Dulu -->
                        <input type="hidden" name="payment_choice" value="open_bill">
                        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-950 space-y-1">
                            <div class="flex items-center gap-2 font-black text-xs text-emerald-900">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Otomatis Masuk ke Open Bill Meja</span>
                            </div>
                            <p class="text-[11px] text-emerald-800 leading-relaxed">
                                Anda <strong>tidak perlu membayar sekarang</strong>. Pesanan tambahan ini langsung dikirim ke Dapur dan diakumulasikan ke tagihan meja Anda (Nomor Bill: {{ $activeOpenBill->bill_number }}).
                            </p>
                        </div>
                    @else
                        <!-- Pilihan Metode Pembayaran Khusus Non-Open Bill -->
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Metode Pembayaran *</label>
                            <div class="space-y-2.5">
                                <!-- 1. Midtrans Snap Online Gateway -->
                                <label class="flex items-start gap-3 p-3.5 rounded-2xl border-2 border-amber-500 bg-amber-50/40 cursor-pointer transition payment-card" id="card-midtrans">
                                    <input type="radio" name="payment_choice" value="midtrans" class="mt-0.5 text-amber-600 focus:ring-amber-500" checked onchange="handlePaymentChange(this.value)">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-extrabold text-slate-900">Bayar Online Sekarang (QRIS / E-Wallet)</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-600 text-white shadow-2xs">Payment Gateway</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Scan QRIS (GoPay, OVO, ShopeePay) atau Virtual Account otomatis via Midtrans.</p>
                                    </div>
                                </label>

                                <!-- 2. Bayar Tunai ke Kasir -->
                                <label class="flex items-start gap-3 p-3.5 rounded-2xl border-2 border-slate-200 bg-white cursor-pointer hover:border-slate-300 transition payment-card" id="card-cash">
                                    <input type="radio" name="payment_choice" value="cash" class="mt-0.5 text-amber-600 focus:ring-amber-500" onchange="handlePaymentChange(this.value)">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-900">Bayar Tunai di Kasir</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">Tunai</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kirim pesanan ke dapur sekarang, lalu Anda dapat langsung menuju ke kasir untuk membayar tunai.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="paymentNotice" class="bg-amber-50 p-3.5 rounded-2xl text-[11px] text-amber-900 leading-relaxed border border-amber-200/60">
                            Setelah menekan <strong>Kirim & Bayar Online</strong>, jendela pembayaran Midtrans akan terbuka untuk pembayaran instan via QRIS / E-Wallet.
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-400 uppercase font-bold block">Total Pesanan</span>
                        <div id="modalCartTotal" class="text-xl font-black text-slate-900">Rp 0</div>
                    </div>
                    <button type="submit" id="btnSubmitOrder"
                        class="px-6 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-lg shadow-amber-600/20 transition transform hover:scale-105 active:scale-95 flex items-center gap-2">
                        <span id="btnSubmitText">
                            {{ $activeOpenBill ? 'Kirim ke Dapur (Tanpa Bayar Sekarang) &rarr;' : 'Kirim & Bayar Online &rarr;' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Request Open Bill ke Kasir -->
    <div id="requestOpenBillModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-amber-200 animate-in fade-in zoom-in-95 duration-200">
            <div class="bg-gradient-to-r from-stone-900 via-amber-950 to-stone-900 p-5 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-amber-300 tracking-wider">Layanan Meja</span>
                    <h3 class="text-base font-black font-serif-resto">Permintaan Buka Open Bill</h3>
                </div>
                <button type="button" onclick="closeRequestOpenBillModal()" class="text-stone-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('order.request_open_bill', $table->table_number) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-2xl text-xs text-amber-900 leading-relaxed">
                    <p class="font-bold">Keuntungan Open Bill:</p>
                    <ul class="list-disc list-inside mt-1 text-[11px] text-amber-800 space-y-0.5">
                        <li>Bebas memesan menu tambahan kapan saja tanpa bayar berulang-ulang.</li>
                        <li>Pesanan langsung dikirim ke Dapur.</li>
                        <li>Tagihan diakumulasikan dan dapat dilunasi sekaligus sebelum pulang.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemesan / Meja *</label>
                    <input type="text" name="customer_name" required placeholder="Misal: Budi / Kakak Meja {{ $table->table_number }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan untuk Kasir (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Misal: Makan bersama rombongan, mohon open bill..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeRequestOpenBillModal()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-md shadow-amber-600/25 transition transform active:scale-95">
                        Kirim Permintaan ke Kasir &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pelunasan Tagihan Open Bill (Sudah Mau Membayar) -->
    @if($activeOpenBill)
        <div id="settlementModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-emerald-300 animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-stone-900 via-emerald-950 to-stone-900 p-5 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-emerald-300 tracking-wider">Pelunasan Tagihan Meja</span>
                        <h3 class="text-base font-black font-serif-resto">Pelunasan Open Bill: {{ $table->name }}</h3>
                    </div>
                    <button type="button" onclick="closeSettlementModal()" class="text-stone-400 hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Ringkasan Total Tagihan -->
                    <div class="p-4 rounded-2xl bg-[#faf8f4] border border-[#ebdcc4] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 uppercase block">Total Akumulasi Tagihan</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $activeOpenBill->bill_number }} &bull; {{ $activeOpenBill->sales->count() }} pesanan</span>
                        </div>
                        <div class="text-2xl font-black text-amber-950 font-serif-resto">
                            Rp {{ number_format($activeOpenBill->total_amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Pilihan Cara Pelunasan -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Pilih Cara Pembayaran:</label>

                        <!-- Pilihan 1: Bayar Online Instan via Midtrans (QRIS / E-Wallet) -->
                        <div class="p-4 rounded-2xl border-2 border-emerald-300 bg-emerald-50/50 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-emerald-950">1. Bayar Online Langsung (QRIS / E-Wallet)</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 text-white">Instan</span>
                            </div>
                            <p class="text-[11px] text-emerald-800 leading-relaxed">
                                Bayar langsung tanpa perlu mengantre ke kasir. Scan QRIS melalui GoPay, OVO, BCA Mobile, atau ShopeePay.
                            </p>
                            <button type="button" id="btnPayOpenBillMidtrans" onclick="payOpenBillWithMidtrans()"
                                class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Bayar Sekarang via Midtrans (Rp {{ number_format($activeOpenBill->total_amount, 0, ',', '.') }}) &rarr;</span>
                            </button>
                        </div>

                        <!-- Pilihan 2: Bayar di Kasir (Tunai / EDC Kasir) -->
                        <div class="p-4 rounded-2xl border-2 border-slate-200 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900">2. Bayar di Meja Kasir (Tunai / Debit / EDC)</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">Kasir</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Silakan menuju ke meja kasir untuk melunasi tagihan <strong>{{ $table->name }}</strong> senilai <strong>Rp {{ number_format($activeOpenBill->total_amount, 0, ',', '.') }}</strong>. Kasir akan mencetak struk resmi dan menerima pembayaran Anda.
                            </p>
                            <div class="pt-1">
                                <button type="button" onclick="closeSettlementModal()"
                                    class="w-full py-2.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                                    Saya Akan Menuju ke Kasir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        let cart = {};
        const isActiveOpenBill = {{ $activeOpenBill ? 'true' : 'false' }};

        function scrollToMenu() {
            const el = document.getElementById('menuCatalogSection');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function openRequestOpenBillModal() {
            const modal = document.getElementById('requestOpenBillModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeRequestOpenBillModal() {
            const modal = document.getElementById('requestOpenBillModal');
            if (modal) modal.classList.add('hidden');
        }

        function openSettlementModal() {
            const modal = document.getElementById('settlementModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeSettlementModal() {
            const modal = document.getElementById('settlementModal');
            if (modal) modal.classList.add('hidden');
        }

        async function payOpenBillWithMidtrans() {
            const btn = document.getElementById('btnPayOpenBillMidtrans');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Menyiapkan Pembayaran...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch("{{ route('order.open_bill_snap_token', $table->table_number) }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                const data = await response.json();
                if (!response.ok || !data.token) {
                    throw new Error(data.message || 'Gagal membuat tagihan online.');
                }

                if (window.snap) {
                    window.snap.pay(data.token, {
                        onSuccess: async function(result) {
                            try {
                                await fetch("{{ route('order.open_bill_midtrans_success', $table->table_number) }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        transaction_id: result.transaction_id || result.order_id,
                                        result: result
                                    })
                                });
                            } catch (err) {
                                console.error('Callback error:', err);
                            }
                            window.location.reload();
                        },
                        onPending: function() {
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert('Pembayaran belum selesai: ' + (result.status_message || 'Silakan bayar di kasir.'));
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        },
                        onClose: function() {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    });
                } else {
                    alert('Midtrans Snap SDK tidak termuat. Silakan bayar langsung di kasir.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                alert('Terjadi kendala: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function addToCart(id, name, price) {
            if (!cart[id]) {
                cart[id] = { id, name, price, quantity: 1, notes: '' };
            } else {
                cart[id].quantity += 1;
            }
            updateProductUI(id);
            updateCartSummary();
        }

        function increaseQty(id) {
            if (cart[id]) {
                cart[id].quantity += 1;
                updateProductUI(id);
                updateCartSummary();
            }
        }

        function decreaseQty(id) {
            if (cart[id]) {
                cart[id].quantity -= 1;
                if (cart[id].quantity <= 0) {
                    delete cart[id];
                }
                updateProductUI(id);
                updateCartSummary();
            }
        }

        function updateProductUI(id) {
            const btnAdd = document.getElementById(`btn-add-${id}`);
            const counter = document.getElementById(`counter-${id}`);
            const qtyText = document.getElementById(`qty-${id}`);

            if (cart[id] && cart[id].quantity > 0) {
                if (btnAdd) btnAdd.classList.add('hidden');
                if (counter) counter.classList.remove('hidden');
                if (counter) counter.classList.add('inline-flex');
                if (qtyText) qtyText.innerText = cart[id].quantity;
            } else {
                if (btnAdd) btnAdd.classList.remove('hidden');
                if (counter) counter.classList.add('hidden');
                if (counter) counter.classList.remove('inline-flex');
                if (qtyText) qtyText.innerText = '0';
            }
        }

        function updateCartSummary() {
            const floatingCart = document.getElementById('floatingCart');
            const cartCountBadge = document.getElementById('cartCountBadge');
            const cartTotalText = document.getElementById('cartTotalText');

            let totalQty = 0;
            let totalPrice = 0;

            for (const key in cart) {
                totalQty += cart[key].quantity;
                totalPrice += cart[key].quantity * cart[key].price;
            }

            if (totalQty > 0) {
                floatingCart.classList.remove('hidden');
                cartCountBadge.innerText = totalQty;
                cartTotalText.innerText = 'Rp ' + Number(totalPrice).toLocaleString('id-ID');
            } else {
                floatingCart.classList.add('hidden');
                closeCartModal();
            }
        }

        function openCartModal() {
            renderCartItems();
            document.getElementById('cartModal').classList.remove('hidden');
        }

        function closeCartModal() {
            document.getElementById('cartModal').classList.add('hidden');
        }

        function renderCartItems() {
            const list = document.getElementById('cartItemsList');
            const modalTotal = document.getElementById('modalCartTotal');
            list.innerHTML = '';

            let totalPrice = 0;
            let index = 0;

            for (const key in cart) {
                const item = cart[key];
                const subtotal = item.quantity * item.price;
                totalPrice += subtotal;

                const row = document.createElement('div');
                row.className = 'py-3 flex flex-col gap-2';
                row.innerHTML = `
                    <input type="hidden" name="items[${index}][id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-bold text-xs text-slate-800">${item.name}</h5>
                            <span class="text-[11px] text-slate-400">Rp ${Number(item.price).toLocaleString('id-ID')} / porsi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg p-1">
                                <button type="button" onclick="decreaseQty(${item.id}); renderCartItems();"
                                    class="w-5 h-5 rounded bg-white text-slate-800 flex items-center justify-center font-bold text-xs hover:bg-slate-200">-</button>
                                <span class="font-bold text-xs px-1 text-slate-900">${item.quantity}</span>
                                <button type="button" onclick="increaseQty(${item.id}); renderCartItems();"
                                    class="w-5 h-5 rounded bg-amber-600 text-white flex items-center justify-center font-bold text-xs hover:bg-amber-700">+</button>
                            </div>
                            <span class="font-black text-xs text-slate-900 min-w-[4.5rem] text-right">
                                Rp ${Number(subtotal).toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>
                    <div>
                        <input type="text" name="items[${index}][notes]" value="${item.notes || ''}"
                            onchange="cart[${item.id}].notes = this.value"
                            placeholder="Catatan porsi ini (misal: pedas sedang)..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-amber-500">
                    </div>
                `;
                list.appendChild(row);
                index++;
            }

            modalTotal.innerText = 'Rp ' + Number(totalPrice).toLocaleString('id-ID');
        }

        function filterMenu() {
            const query = document.getElementById('menuSearch').value.toLowerCase();
            const items = document.querySelectorAll('.menu-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        function filterCategory(categoryId) {
            document.querySelectorAll('.cat-tab').forEach(tab => {
                tab.classList.remove('bg-amber-600', 'text-white');
                tab.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });

            const activeTab = document.getElementById(`catTab-${categoryId}`);
            if (activeTab) {
                activeTab.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                activeTab.classList.add('bg-amber-600', 'text-white');
            }

            const sections = document.querySelectorAll('.category-section');
            if (categoryId === 'all') {
                sections.forEach(s => s.classList.remove('hidden'));
            } else {
                sections.forEach(s => {
                    if (s.id === categoryId) {
                        s.classList.remove('hidden');
                    } else {
                        s.classList.add('hidden');
                    }
                });
            }
        }

        function handlePaymentChange(val) {
            document.querySelectorAll('.payment-card').forEach(c => {
                c.classList.remove('border-amber-500', 'bg-amber-50/40', 'border-slate-300');
                c.classList.add('border-slate-200', 'bg-white');
            });

            const card = document.getElementById(`card-${val}`);
            const notice = document.getElementById('paymentNotice');
            const submitText = document.getElementById('btnSubmitText');

            if (val === 'midtrans') {
                if (card) {
                    card.classList.remove('border-slate-200', 'bg-white');
                    card.classList.add('border-amber-500', 'bg-amber-50/40');
                }
                submitText.innerHTML = 'Kirim & Bayar Online &rarr;';
                notice.innerHTML = 'Setelah menekan <strong>Kirim & Bayar Online</strong>, jendela pembayaran Midtrans akan terbuka untuk pembayaran instan via QRIS / E-Wallet.';
            } else if (val === 'cash') {
                if (card) {
                    card.classList.remove('border-slate-200', 'bg-white');
                    card.classList.add('border-amber-500', 'bg-amber-50/40');
                }
                submitText.innerHTML = 'Kirim Pesanan (Bayar di Kasir) &rarr;';
                notice.innerHTML = 'Pesanan akan langsung dikirim ke Dapur. Anda dapat langsung menuju kasir untuk membayar tunai.';
            } else if (val === 'open_bill') {
                if (card) {
                    card.classList.remove('border-slate-200', 'bg-white');
                    card.classList.add('border-emerald-500', 'bg-emerald-50/60');
                }
                submitText.innerHTML = 'Kirim Pesanan (Gabung Open Bill) &rarr;';
                notice.innerHTML = 'Pesanan akan langsung diproses Dapur dan dimasukkan ke Open Bill meja. Anda tidak perlu bayar sekarang, melainkan saat selesai makan di kasir.';
            }
        }

        document.getElementById('checkoutForm')?.addEventListener('submit', async function(e) {
            const selectedMethod = document.querySelector('input[name="payment_choice"]:checked')?.value || (isActiveOpenBill ? 'open_bill' : 'cash');
            if (selectedMethod !== 'midtrans') {
                // Biarkan form submit biasa untuk tunai atau open bill
                return true;
            }

            e.preventDefault();

            const btn = document.getElementById('btnSubmitOrder');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyiapkan Pembayaran...
            `;

            try {
                const formData = new FormData(this);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal membuat pesanan.');
                }

                if (data.snap_token && window.snap) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: async function(result) {
                            try {
                                await fetch(data.redirect_url + '/midtrans-success', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        transaction_id: result.transaction_id || result.order_id,
                                        result: result
                                    })
                                });
                            } catch (err) {
                                console.error('Callback error:', err);
                            }
                            window.location.href = data.redirect_url;
                        },
                        onPending: function(result) {
                            window.location.href = data.redirect_url;
                        },
                        onError: function(result) {
                            alert('Pembayaran belum selesai: ' + (result.status_message || 'Silakan bayar di kasir.'));
                            window.location.href = data.redirect_url;
                        },
                        onClose: function() {
                            window.location.href = data.redirect_url;
                        }
                    });
                } else {
                    window.location.href = data.redirect_url;
                }
            } catch (err) {
                alert('Terjadi kendala: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
