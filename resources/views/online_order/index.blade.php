<!DOCTYPE html>
<html lang="id" class="h-full bg-[#fbf9f4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pemesanan Online - Fira Cake Bakery & Pastry</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ $midtransSnapUrl ?? config('services.midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/snap.js') }}" data-client-key="{{ $midtransClientKey ?? config('services.midtrans.client_key', 'SB-Mid-client-cclA53nEGemMIzdKixoGyyx7') }}"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-serif-classic {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-full flex flex-col text-[#26211a] bg-[#fbf9f4] antialiased selection:bg-[#c59b27] selection:text-white">

    <!-- Container Utama: Mobile-First Responsive Shell -->
    <div class="w-full max-w-md mx-auto min-h-screen flex flex-col bg-white shadow-xl relative pb-28 border-x border-[#ede7dc]">

        <!-- Top Header Toko (Mobile App Bar) -->
        <header class="bg-white border-b border-[#eee7dc] px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    @php
                        $storeLogo = !empty($settings['app_logo']) ? asset('storage/' . $settings['app_logo']) : asset('images/logo.png');
                    @endphp
                    <img src="{{ $storeLogo }}" alt="{{ $settings['store_name'] ?? 'Fira Cake' }}"
                        class="w-10 h-10 rounded-xl object-contain shadow-2xs border border-[#eee7dc] p-0.5 bg-[#fdfbf7] shrink-0"
                        onerror="this.src='{{ asset('images/default-cake.png') }}'">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-sm font-black text-[#26211a] tracking-tight">{{ $settings['store_name'] ?? 'Fira Cake' }}</h1>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-[#c59b27] text-stone-950">
                                Online
                            </span>
                        </div>
                        <p class="text-[10px] text-[#786e60] leading-tight">Bakery & Pastry &bull; Pesan & Bayar Online</p>
                    </div>
                </div>

                <!-- Status Buka Toko -->
                <div class="flex flex-col items-end">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Buka
                    </span>
                    <span class="text-[9px] text-[#8c8270] mt-0.5 font-medium">{{ $settings['store_hours'] ?? '08:00 - 21:00 WITA' }}</span>
                </div>
            </div>
        </header>

        <!-- 1. Card Informasi Selamat Datang, Deskripsi & Nomor Kontak Toko (Berada di atas Card Pembayaran Midtrans) -->
        <div class="m-4 mb-2.5 bg-gradient-to-br from-[#26211a] to-[#3a3227] text-[#f4efe6] rounded-2xl p-4 shadow-md border border-[#4a4032] space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#c59b27]/20 border border-[#c59b27]/40 text-[#f5d97f] text-[10px] font-black uppercase tracking-wider">
                        <svg class="w-3 h-3 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span>Official Online Store</span>
                    </div>
                    <h2 class="font-serif-classic text-base font-bold text-white tracking-tight">
                        {{ $settings['online_welcome_title'] ?? 'Selamat Datang di Fira Cake!' }}
                    </h2>
                </div>
            </div>

            <p class="text-[11px] text-[#d6cfc2] leading-relaxed">
                {{ $settings['online_welcome_description'] ?? 'Nikmati aneka pilihan roti, bolu fresh, kue tart, dan pastry premium kualitas terbaik kami langsung dibuat segar setiap hari.' }}
            </p>

            <div class="pt-2.5 border-t border-[#4a4032]/80 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                <div class="flex items-center gap-1.5 text-[#f5d97f]">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="font-bold text-white">Kontak Toko:</span>
                    <span class="font-mono text-[#f5d97f]">{{ $settings['store_phone'] ?? '082131117996' }}</span>
                </div>

                @php
                    $cleanStorePhone = preg_replace('/[^0-9]/', '', $settings['store_phone'] ?? '082131117996');
                    if (str_starts_with($cleanStorePhone, '0')) {
                        $storeWaNumber = '62' . substr($cleanStorePhone, 1);
                    } elseif (!str_starts_with($cleanStorePhone, '62')) {
                        $storeWaNumber = '62' . $cleanStorePhone;
                    } else {
                        $storeWaNumber = $cleanStorePhone;
                    }
                    $storeWaUrl = "https://api.whatsapp.com/send?phone={$storeWaNumber}&text=" . urlencode("Halo Fira Cake, saya ingin bertanya seputar pesanan online...");
                @endphp
                <a href="{{ $storeWaUrl }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold shadow-xs transition active:scale-95">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                    </svg>
                    <span>Hubungi WA</span>
                </a>
            </div>
        </div>

        <!-- 2. Card Pembayaran Online via Midtrans Payment Gateway (Tepat di bawah Card Info Toko) -->
        <div class="mx-4 mb-3 p-3 rounded-2xl bg-gradient-to-r from-[#faf7f0] to-[#f4eee1] border border-[#e5dfd3] flex items-center justify-between gap-2 shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 border border-amber-300/80 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-[#26211a] truncate">
                        Pembayaran Online via <span class="text-[#8a681c] font-black">Midtrans</span>
                    </div>
                    <div class="text-[10px] text-[#786e60] truncate">
                        QRIS, GoPay, ShopeePay, VA & Transfer Bank
                    </div>
                </div>
            </div>
            <span class="text-[9px] font-black uppercase tracking-wider text-amber-900 bg-amber-200/80 border border-amber-300/80 px-2 py-0.5 rounded-lg shrink-0">
                Wajib Lunas
            </span>
        </div>

        <!-- 3. Sticky Search Bar & Category Navigation -->
        <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-y border-[#eee7dc] px-4 py-2.5 space-y-2 shadow-2xs">
            <!-- Search Bar Menu -->
            <div class="relative">
                <input type="text" id="onlineSearchInput" oninput="handleOnlineSearch()"
                    placeholder="Cari roti, bolu, kue tart, pastry..."
                    class="w-full bg-[#fbf9f4] border border-[#e0d7c7] rounded-xl pl-9 pr-8 py-2 text-xs text-[#26211a] placeholder:text-[#998f80] focus:outline-none focus:ring-1 focus:ring-[#c59b27] transition">
                <svg class="w-4 h-4 text-[#8c8270] absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <button type="button" id="clearSearchBtn" onclick="clearOnlineSearch()" class="hidden absolute right-2.5 top-2.5 text-[#998f80] hover:text-[#26211a] text-xs font-bold">
                    &times;
                </button>
            </div>

            <!-- Category Pills Bar (Horizontal Scrollable) -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                <button type="button" onclick="selectOnlineCategory('all')" id="catTab-all"
                    class="online-cat-tab px-3 py-1.5 rounded-xl text-[11px] font-black transition shrink-0 bg-[#c59b27] text-stone-950 shadow-2xs">
                    Semua Menu
                </button>
                @foreach($categories as $category)
                    <button type="button" onclick="selectOnlineCategory('{{ $category->id }}')" id="catTab-{{ $category->id }}"
                        class="online-cat-tab px-3 py-1.5 rounded-xl text-[11px] font-semibold transition shrink-0 bg-[#f7f4ee] hover:bg-[#ede7da] text-[#6b6252] border border-[#e5dfd3]">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Menu Product List -->
        <main class="flex-1 p-4 space-y-4">
            @php $totalProductCount = 0; @endphp
            @foreach($categories as $category)
                @if($category->products->isNotEmpty())
                    <section class="online-category-section space-y-2.5" data-category-id="{{ $category->id }}">
                        <div class="flex items-center justify-between border-b border-[#f0ebd9] pb-1.5">
                            <h2 class="font-serif-classic text-sm font-bold text-[#26211a] flex items-center gap-1.5">
                                <span>{{ $category->name }}</span>
                                <span class="text-[10px] font-sans font-medium text-[#8c8270]">({{ $category->products->count() }})</span>
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach($category->products as $product)
                                @php $totalProductCount++; @endphp
                                <div class="online-product-card bg-[#ffffff] border border-[#eee7dc] rounded-2xl p-3 shadow-2xs hover:border-[#c59b27]/60 transition flex items-center justify-between gap-3"
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ strtolower($product->name) }}"
                                    data-product-category="{{ $category->id }}">

                                    <!-- Gambar / Thumbnail Menu -->
                                    <div class="w-18 h-18 rounded-xl bg-[#faf7f0] border border-[#ede7dc] flex items-center justify-center shrink-0 overflow-hidden relative">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-[#faf7f0] to-[#eee7dc] text-[#c59b27]">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Informasi Nama, Deskripsi, Harga -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-xs font-bold text-[#26211a] truncate leading-tight">{{ $product->name }}</h3>
                                        @if($product->description)
                                            <p class="text-[10px] text-[#786e60] line-clamp-1 mt-0.5">{{ $product->description }}</p>
                                        @endif
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span class="text-xs font-black text-amber-800 font-serif-classic">
                                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[9px] text-[#8c8270]">/ {{ $product->unit ?? 'Pcs' }}</span>
                                        </div>
                                    </div>

                                    <!-- Action Stepper (+ / - Button) -->
                                    <div class="shrink-0 flex items-center justify-end">
                                        <!-- Tombol Tambah Pertama Kali -->
                                        <button type="button" id="btnAdd-{{ $product->id }}"
                                            onclick="addToOnlineCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$product->selling_price }})"
                                            class="px-3 py-1.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] text-stone-950 text-[11px] font-black transition active:scale-95 flex items-center gap-1 shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Pesan</span>
                                        </button>

                                        <!-- Stepper Jumlah Saat Sudah di Keranjang -->
                                        <div id="stepper-{{ $product->id }}" class="hidden items-center gap-1.5 bg-[#fbf9f4] border border-[#dcd4c5] p-1 rounded-xl">
                                            <button type="button" onclick="decrementOnlineItem({{ $product->id }})"
                                                class="w-6 h-6 rounded-lg bg-white text-[#26211a] hover:bg-stone-200 font-bold flex items-center justify-center transition border border-[#ede7dc] text-xs">
                                                &minus;
                                            </button>
                                            <span id="qtyDisplay-{{ $product->id }}" class="w-5 text-center text-xs font-black text-[#26211a]">
                                                0
                                            </span>
                                            <button type="button" onclick="incrementOnlineItem({{ $product->id }})"
                                                class="w-6 h-6 rounded-lg bg-[#c59b27] text-stone-950 hover:bg-[#a87f1c] font-black flex items-center justify-center transition text-xs shadow-2xs">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <!-- Empty State Search -->
            <div id="emptySearchState" class="hidden py-12 text-center text-[#8c8270]">
                <svg class="w-12 h-12 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs font-bold text-[#5c5344]">Menu tidak ditemukan</p>
                <p class="text-[11px] text-[#8c8270] mt-0.5">Coba kata kunci lain atau pilih kategori di atas.</p>
            </div>

            <!-- Store Footer Information -->
            <footer class="pt-6 pb-4 border-t border-[#eee7dc] text-center space-y-2 text-[#786e60]">
                <div class="flex items-center justify-center gap-1.5 font-serif-classic font-bold text-xs text-[#26211a]">
                    <span>{{ $settings['store_name'] ?? 'Fira Cake Bakery & Pastry' }}</span>
                </div>
                <p class="text-[10px] text-[#8c8270] max-w-xs mx-auto leading-relaxed">
                    {{ $settings['store_address'] ?? 'BISOLPIN, Ruko Ruby, Jl. Boulevard Rubi II, Masale, Panakkukang, Makassar' }}
                </p>
                <div class="text-[10px] flex items-center justify-center gap-3 text-[#8c8270] pt-1">
                    <span>Jam Buka: {{ $settings['store_hours'] ?? '08:00 - 21:00 WITA' }}</span>
                    <span>&bull;</span>
                    <a href="{{ $storeWaUrl }}" target="_blank" class="text-emerald-700 font-bold hover:underline">WA: {{ $settings['store_phone'] ?? '082131117996' }}</a>
                </div>
            </footer>
        </main>

        <!-- Sticky Floating Bottom Cart Bar (Muncul Otomatis saat ada pesanan) -->
        <div id="floatingCartBar" class="hidden fixed bottom-0 inset-x-0 z-40 p-3 max-w-md mx-auto transition-transform">
            <div class="bg-[#26211a] text-white rounded-2xl p-3 shadow-2xl border border-stone-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#c59b27] text-stone-950 font-black text-xs flex items-center justify-center shadow-md">
                        <span id="floatingTotalQty">0</span>
                    </div>
                    <div>
                        <div class="text-[10px] text-stone-300 leading-tight">Total Keranjang</div>
                        <div id="floatingTotalPrice" class="font-serif-classic font-bold text-sm text-[#e8dfcf]">Rp 0</div>
                    </div>
                </div>

                <button type="button" onclick="openCheckoutModal()"
                    class="px-4 py-2.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-xs transition flex items-center gap-1.5 shadow-sm">
                    <span>Lanjut Pesan</span>
                    <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Slide-Up Checkout Modal / Drawer -->
        <div id="checkoutModal" class="hidden fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs flex items-end sm:items-center justify-center">
            <div class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl max-h-[90vh] flex flex-col shadow-2xl border-t sm:border border-[#e5dfd3] overflow-hidden" onclick="event.stopPropagation()">
                
                <!-- Modal Header -->
                <div class="px-5 py-3.5 border-b border-[#f0ebd9] flex items-center justify-between bg-[#faf7f0]">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="font-serif-classic font-bold text-sm text-[#26211a]">Keranjang & Data Pemesan</h3>
                    </div>
                    <button type="button" onclick="closeCheckoutModal()" class="w-7 h-7 rounded-lg bg-stone-200/60 hover:bg-stone-300 text-stone-600 flex items-center justify-center text-base font-bold">
                        &times;
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
                    
                    <!-- Daftar Item Pesanan -->
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#786e60] mb-2 flex items-center justify-between">
                            <span>Item Pesanan Anda</span>
                            <button type="button" onclick="confirmClearOnlineCart()" class="text-rose-600 hover:text-rose-800 text-[10px] font-semibold">
                                Kosongkan
                            </button>
                        </div>
                        <div id="cartItemsList" class="space-y-2 max-h-48 overflow-y-auto pr-1 divide-y divide-[#f0ebd9]">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                    <!-- Form Data Pelanggan -->
                    <div class="bg-[#faf7f0] p-3.5 rounded-2xl border border-[#e5dfd3] space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#786e60]">
                            Data Pemesan & Kontak WhatsApp
                        </div>

                        <!-- Nama Pelanggan -->
                        <div>
                            <label class="block text-xs font-bold text-[#26211a] mb-1">
                                Nama Lengkap <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" id="custName" required placeholder="Contoh: Budi Santoso"
                                class="w-full bg-white border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#26211a] focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                        </div>

                        <!-- Nomor Telepon Pelanggan (Wajib untuk Chat WA Kasir) -->
                        <div>
                            <label class="block text-xs font-bold text-[#26211a] mb-1">
                                Nomor Telepon / WhatsApp <span class="text-rose-600">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 font-bold text-[#786e60] text-xs">+62</span>
                                <input type="tel" id="custPhone" required placeholder="81234567890" inputmode="numeric"
                                    class="w-full bg-white border border-[#dcd4c5] rounded-xl pl-12 pr-3 py-2 text-xs text-[#26211a] focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                            </div>
                            <p class="text-[10px] text-[#786e60] mt-1 leading-tight">
                                Kasir toko akan mengonfirmasi pesanan Anda via WhatsApp melalui nomor ini.
                            </p>
                        </div>

                        <!-- Alamat Pengiriman / Catatan Pengantaran -->
                        <div>
                            <label class="block text-xs font-bold text-[#26211a] mb-1">
                                Alamat Pengantaran / Catatan Lokasi (Opsional)
                            </label>
                            <textarea id="custAddress" rows="2" placeholder="Contoh: Jl. Boulevard No. 12, samping minimarket (atau tulis: Ambil Langsung di Toko)"
                                class="w-full bg-white border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#26211a] focus:outline-none focus:ring-1 focus:ring-[#c59b27]"></textarea>
                        </div>

                        <!-- Catatan Pesanan -->
                        <div>
                            <label class="block text-xs font-bold text-[#26211a] mb-1">
                                Catatan Khusus Pesanan (Opsional)
                            </label>
                            <input type="text" id="custNotes" placeholder="Contoh: Lilin ulang tahun, ucapan di atas kue..."
                                class="w-full bg-white border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#26211a] focus:outline-none focus:ring-1 focus:ring-[#c59b27]">
                        </div>
                    </div>

                    <!-- Metode Pembayaran: Wajib Payment Gateway Midtrans -->
                    <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/90 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Metode Pembayaran</span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-200/80 text-amber-900">Midtrans Gateway</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white p-2.5 rounded-xl border border-amber-200 shadow-2xs">
                            <div class="w-8 h-8 rounded-lg bg-stone-900 text-[#c59b27] flex items-center justify-center font-black text-[11px] shrink-0">
                                SNAP
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-[#26211a]">Midtrans Payment Gateway</div>
                                <div class="text-[10px] text-[#786e60]">QRIS (GoPay, OVO, ShopeePay, Dana), BCA, Mandiri VA, dll.</div>
                            </div>
                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                        </div>
                        <p class="text-[10px] text-amber-900/90 leading-tight">
                            Pesanan online wajib lunas melalui Midtrans Payment Gateway. Setelah pembayaran selesai, pesanan Anda otomatis langsung masuk ke antrean kasir & dapur Fira Cake.
                        </p>
                    </div>

                    <!-- Error Alert -->
                    <div id="checkoutError" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs"></div>

                    <!-- Ringkasan Total Tagihan -->
                    <div class="p-3 bg-[#faf7f0] rounded-xl border border-[#e5dfd3] space-y-1">
                        <div class="flex justify-between text-[#786e60]">
                            <span>Subtotal Menu</span>
                            <span id="modalSubtotal" class="font-bold text-[#26211a]">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-[#786e60]">
                            <span>Biaya Layanan Midtrans</span>
                            <span class="font-bold text-emerald-700">Gratis (Rp 0)</span>
                        </div>
                        <div class="pt-2 border-t border-[#e5dfd3] flex justify-between items-center">
                            <span class="font-bold text-xs text-[#26211a]">Total Tagihan:</span>
                            <span id="modalTotalPay" class="font-serif-classic font-black text-base text-amber-900">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-[#f0ebd9] bg-white flex items-center gap-2">
                    <button type="button" onclick="closeCheckoutModal()"
                        class="px-4 py-2.5 rounded-xl bg-[#f0ebd9] text-[#5c5344] font-bold text-xs hover:bg-[#e4dec8] transition active:scale-95">
                        Batal
                    </button>
                    <button type="button" id="btnPayOnline" onclick="processOnlineCheckout()"
                        class="flex-1 py-2.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Bayar Sekarang via Midtrans</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Simulasi / Fallback Pembayaran Midtrans (Sandbox / Tanpa Jaringan Eksternal) -->
        <div id="qrisPaymentModal" class="hidden fixed inset-0 z-50 bg-stone-950/85 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xs w-full p-5 text-center shadow-2xl border border-[#e5dfd3] space-y-3.5" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-2 border-b border-[#f0e8db]">
                    <div class="flex items-center gap-1.5 text-left">
                        <div class="w-6 h-6 rounded-lg bg-stone-900 text-[#c59b27] font-black text-[10px] flex items-center justify-center">
                            SNAP
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-[#26211a] leading-tight">Midtrans Simulator</h3>
                            <p class="text-[9px] text-[#786e60]">Fira Cake Bakery & Pastry</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePaymentGatewayModal()" class="text-stone-400 hover:text-stone-600 text-lg font-bold">
                        &times;
                    </button>
                </div>

                <p class="text-[10px] text-[#6b6252]">Silakan verifikasi pembayaran pesanan online Anda:</p>

                <!-- QRIS Code Image Simulation -->
                <div class="p-3 bg-white rounded-2xl border-2 border-dashed border-[#c59b27] inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=FIRA-CAKE-ONLINE-ORDER-PAYMENT"
                        alt="QRIS Midtrans" class="w-36 h-36 mx-auto rounded-lg">
                </div>

                <div class="p-2.5 rounded-xl bg-[#faf7f0] border border-[#e8dfcf]">
                    <div class="text-[10px] text-[#786e60]">Total yang Harus Dibayar:</div>
                    <div id="qrisModalPayAmount" class="font-serif-classic text-lg font-black text-emerald-800">Rp 0</div>
                    <div class="text-[9px] text-[#8c8270] mt-0.5 font-medium">QRIS &bull; GoPay &bull; BCA &bull; Mandiri &bull; DANA</div>
                </div>

                <div class="pt-1 space-y-2">
                    <button type="button" id="btnSimulatePaySuccess" onclick="processFallbackMidtransSuccess()"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi Pembayaran Midtrans Lunas</span>
                    </button>
                    <button type="button" onclick="closePaymentGatewayModal()"
                        class="w-full py-1.5 rounded-xl bg-[#faf7f0] hover:bg-[#ede7da] text-[#786e60] text-xs font-semibold transition">
                        Kembali / Ubah Pesanan
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- JavaScript Application Logic -->
    <script>
        let onlineCart = {};
        let currentOrderData = null;

        // Inisialisasi Cart dari LocalStorage jika ada
        try {
            const saved = localStorage.getItem('fira_online_cart');
            if (saved) {
                onlineCart = JSON.parse(saved);
            }
        } catch (e) {
            onlineCart = {};
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderOnlineCart();
        });

        function saveCart() {
            try {
                localStorage.setItem('fira_online_cart', JSON.stringify(onlineCart));
            } catch (e) {}
        }

        // Tambah Produk ke Keranjang
        function addToOnlineCart(id, name, price) {
            if (!onlineCart[id]) {
                onlineCart[id] = {
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    notes: ''
                };
            } else {
                onlineCart[id].quantity += 1;
            }
            saveCart();
            renderOnlineCart();
        }

        // Tambah Qty
        function incrementOnlineItem(id) {
            if (onlineCart[id]) {
                onlineCart[id].quantity += 1;
                saveCart();
                renderOnlineCart();
            }
        }

        // Kurangi Qty
        function decrementOnlineItem(id) {
            if (onlineCart[id]) {
                onlineCart[id].quantity -= 1;
                if (onlineCart[id].quantity <= 0) {
                    delete onlineCart[id];
                }
                saveCart();
                renderOnlineCart();
            }
        }

        // Kosongkan Keranjang
        function confirmClearOnlineCart() {
            if (confirm('Kosongkan semua item dari keranjang?')) {
                onlineCart = {};
                saveCart();
                renderOnlineCart();
                closeCheckoutModal();
            }
        }

        function getOnlineCartQty() {
            return Object.values(onlineCart).reduce((sum, item) => sum + item.quantity, 0);
        }

        function getOnlineCartTotal() {
            return Object.values(onlineCart).reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        function formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number || 0);
        }

        // Render Antarmuka Keranjang & Tombol Produk
        function renderOnlineCart() {
            const totalQty = getOnlineCartQty();
            const totalPrice = getOnlineCartTotal();

            // 1. Update Floating Bottom Bar
            const bar = document.getElementById('floatingCartBar');
            if (totalQty > 0) {
                bar.classList.remove('hidden');
                document.getElementById('floatingTotalQty').innerText = totalQty;
                document.getElementById('floatingTotalPrice').innerText = formatRupiah(totalPrice);
            } else {
                bar.classList.add('hidden');
            }

            // 2. Update Tombol Tiap Produk di Menu
            document.querySelectorAll('.online-product-card').forEach(card => {
                const pid = card.getAttribute('data-product-id');
                const btnAdd = document.getElementById('btnAdd-' + pid);
                const stepper = document.getElementById('stepper-' + pid);
                const qtySpan = document.getElementById('qtyDisplay-' + pid);

                if (onlineCart[pid] && onlineCart[pid].quantity > 0) {
                    if (btnAdd) btnAdd.classList.add('hidden');
                    if (stepper) stepper.classList.remove('hidden');
                    if (stepper) stepper.classList.add('flex');
                    if (qtySpan) qtySpan.innerText = onlineCart[pid].quantity;
                } else {
                    if (btnAdd) btnAdd.classList.remove('hidden');
                    if (stepper) stepper.classList.add('hidden');
                    if (stepper) stepper.classList.remove('flex');
                }
            });

            // 3. Update Modal Keranjang jika terbuka
            const listContainer = document.getElementById('cartItemsList');
            if (listContainer) {
                document.getElementById('modalSubtotal').innerText = formatRupiah(totalPrice);
                document.getElementById('modalTotalPay').innerText = formatRupiah(totalPrice);

                const items = Object.values(onlineCart);
                if (items.length === 0) {
                    listContainer.innerHTML = '<div class="py-4 text-center text-[#786e60]">Keranjang masih kosong.</div>';
                } else {
                    listContainer.innerHTML = items.map(item => `
                        <div class="py-2 flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-[#26211a] truncate">${item.name}</div>
                                <div class="text-[10px] text-[#786e60]">${formatRupiah(item.price)} x ${item.quantity} = <strong class="text-amber-800">${formatRupiah(item.price * item.quantity)}</strong></div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="decrementOnlineItem(${item.id})" class="w-6 h-6 rounded-lg bg-stone-100 hover:bg-stone-200 border border-stone-300 font-bold flex items-center justify-center text-xs">&minus;</button>
                                <span class="w-4 text-center font-black">${item.quantity}</span>
                                <button type="button" onclick="incrementOnlineItem(${item.id})" class="w-6 h-6 rounded-lg bg-[#c59b27] text-stone-950 font-black flex items-center justify-center text-xs shadow-2xs">+</button>
                            </div>
                        </div>
                    `).join('');
                }
            }
        }

        // Modal Checkout Controllers
        function openCheckoutModal() {
            if (getOnlineCartQty() === 0) {
                alert('Keranjang pesanan masih kosong. Pilih menu favorit Anda terlebih dahulu.');
                return;
            }
            renderOnlineCart();
            document.getElementById('checkoutModal').classList.remove('hidden');
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.add('hidden');
        }

        function closePaymentGatewayModal() {
            document.getElementById('qrisPaymentModal').classList.add('hidden');
        }

        // Proses Checkout & Pembayaran Online via Midtrans
        async function processOnlineCheckout() {
            const errEl = document.getElementById('checkoutError');
            errEl.classList.add('hidden');

            const name = (document.getElementById('custName').value || '').trim();
            let rawPhone = (document.getElementById('custPhone').value || '').trim().replace(/\D/g, '');

            if (!name) {
                errEl.innerText = 'Mohon isi nama lengkap Anda.';
                errEl.classList.remove('hidden');
                document.getElementById('custName').focus();
                return;
            }

            if (!rawPhone || rawPhone.length < 8) {
                errEl.innerText = 'Mohon masukkan nomor telepon WhatsApp yang valid (minimal 8-9 digit).';
                errEl.classList.remove('hidden');
                document.getElementById('custPhone').focus();
                return;
            }

            // Normalisasi nomor telepon: awalan 08xxx atau 62xxx
            let phone = rawPhone;
            if (phone.startsWith('62')) {
                phone = '0' + phone.substring(2);
            } else if (!phone.startsWith('0')) {
                phone = '0' + phone;
            }

            const address = (document.getElementById('custAddress').value || '').trim();
            const notes = (document.getElementById('custNotes').value || '').trim();

            const items = Object.values(onlineCart).map(item => ({
                id: item.id,
                quantity: item.quantity,
                notes: item.notes || null
            }));

            if (items.length === 0) {
                errEl.innerText = 'Keranjang pesanan masih kosong. Silakan pilih menu terlebih dahulu.';
                errEl.classList.remove('hidden');
                return;
            }

            const btn = document.getElementById('btnPayOnline');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menyiapkan Midtrans...</span>
            `;

            try {
                const response = await fetch("{{ route('online_order.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_name: name,
                        customer_phone: phone,
                        address: address,
                        notes: notes,
                        payment_method: 'midtrans',
                        items: items
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const snapToken = data.data.snap_token;
                    const redirectUrl = data.data.redirect_url;
                    const callbackUrl = data.data.success_callback_url;

                    if (snapToken && window.snap && typeof window.snap.pay === 'function') {
                        window.snap.pay(snapToken, {
                            onSuccess: async function (result) {
                                try {
                                    await fetch(callbackUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            transaction_id: result.transaction_id || result.order_id,
                                            order_id: result.order_id
                                        })
                                    });
                                } catch (e) {}
                                onlineCart = {};
                                saveCart();
                                window.location.href = redirectUrl;
                            },
                            onPending: function (result) {
                                onlineCart = {};
                                saveCart();
                                window.location.href = redirectUrl;
                            },
                            onError: function (result) {
                                alert('Pembayaran Midtrans gagal atau dibatalkan.');
                                btn.disabled = false;
                                btn.innerHTML = originalBtnHtml;
                            },
                            onClose: function () {
                                onlineCart = {};
                                saveCart();
                                window.location.href = redirectUrl;
                            }
                        });
                    } else {
                        // Jika window.snap tidak termuat atau sandbox fallback simulator
                        currentOrderData = data.data;
                        document.getElementById('qrisModalPayAmount').innerText = formatRupiah(data.data.total_amount);
                        document.getElementById('qrisPaymentModal').classList.remove('hidden');
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    }
                } else {
                    errEl.innerText = data.message || 'Gagal memproses pesanan online. Silakan coba kembali.';
                    errEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                }
            } catch (err) {
                errEl.innerText = 'Terjadi kesalahan jaringan. Periksa koneksi internet Anda.';
                errEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        }

        async function processFallbackMidtransSuccess() {
            if (!currentOrderData) return;
            const btn = document.getElementById('btnSimulatePaySuccess');
            btn.disabled = true;
            btn.innerHTML = 'Memverifikasi Pembayaran...';

            try {
                await fetch(currentOrderData.success_callback_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        transaction_id: 'MIDTRANS-MANUAL-' + Date.now()
                    })
                });
            } catch (e) {}

            onlineCart = {};
            saveCart();
            window.location.href = currentOrderData.redirect_url;
        }

        // Filter Kategori Menu
        function selectOnlineCategory(catId) {
            document.querySelectorAll('.online-cat-tab').forEach(tab => {
                tab.className = "online-cat-tab px-3 py-1.5 rounded-xl text-[11px] font-semibold transition shrink-0 bg-[#f7f4ee] hover:bg-[#ede7da] text-[#6b6252] border border-[#e5dfd3]";
            });
            const activeTab = document.getElementById('catTab-' + catId);
            if (activeTab) {
                activeTab.className = "online-cat-tab px-3 py-1.5 rounded-xl text-[11px] font-black transition shrink-0 bg-[#c59b27] text-stone-950 shadow-2xs";
            }

            document.querySelectorAll('.online-category-section').forEach(section => {
                if (catId === 'all' || section.getAttribute('data-category-id') === catId) {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            });
        }

        // Pencarian Menu Realtime
        function handleOnlineSearch() {
            const q = (document.getElementById('onlineSearchInput').value || '').toLowerCase().trim();
            const clearBtn = document.getElementById('clearSearchBtn');
            const emptyState = document.getElementById('emptySearchState');

            if (q) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            let foundCount = 0;
            document.querySelectorAll('.online-product-card').forEach(card => {
                const name = card.getAttribute('data-product-name');
                if (!q || name.includes(q)) {
                    card.classList.remove('hidden');
                    card.classList.add('flex');
                    foundCount++;
                } else {
                    card.classList.add('hidden');
                    card.classList.remove('flex');
                }
            });

            document.querySelectorAll('.online-category-section').forEach(section => {
                const visibleInCat = section.querySelectorAll('.online-product-card.flex').length;
                if (visibleInCat === 0 && q) {
                    section.classList.add('hidden');
                } else {
                    section.classList.remove('hidden');
                }
            });

            if (foundCount === 0 && q) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        function clearOnlineSearch() {
            document.getElementById('onlineSearchInput').value = '';
            handleOnlineSearch();
            selectOnlineCategory('all');
        }
    </script>

</body>
</html>
