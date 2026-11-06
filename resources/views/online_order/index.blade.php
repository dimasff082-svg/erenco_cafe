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
    <script src="{{ $midtransSnapUrl ?? config('services.midtrans.snap_url') }}" data-client-key="{{ $midtransClientKey ?? config('services.midtrans.client_key') }}"></script>
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

    <!-- Container Utama: Dibatasi max-w-md / max-w-lg agar seperti Native App di HP & Rapi di Desktop -->
    <div class="w-full max-w-md mx-auto min-h-screen flex flex-col bg-white shadow-xl relative pb-24 border-x border-[#ede7dc]">

        <!-- Top Header Toko (Mobile App Bar) -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#eee7dc] px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-10 h-10 rounded-xl object-contain shadow-2xs border border-[#eee7dc] p-0.5 bg-[#fdfbf7] shrink-0">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-sm font-black text-[#26211a] tracking-tight">Fira Cake</h1>
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
                    <span class="text-[9px] text-[#8c8270] mt-0.5 font-medium">Pengiriman & Ambil</span>
                </div>
            </div>

            <!-- Banner Info Pembayaran Khusus Payment Gateway -->
            <div class="mt-2.5 p-2 rounded-xl bg-[#faf7f0] border border-[#e8dfcf] flex items-center justify-between text-[10px]">
                <div class="flex items-center gap-1.5 text-[#5c5344]">
                    <svg class="w-3.5 h-3.5 text-[#c59b27] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Pembayaran Instan via <strong>Payment Gateway QRIS</strong></span>
                </div>
                <span class="text-[9px] font-bold text-amber-800 bg-amber-100/70 px-1.5 py-0.5 rounded">Wajib Lunas</span>
            </div>

            <!-- Search Bar Menu -->
            <div class="mt-2.5 relative">
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
            <div class="mt-2.5 flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
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
        </header>

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

                    <!-- Metode Pembayaran: Wajib Payment Gateway (QRIS) -->
                    <!-- Metode Pembayaran: Wajib Payment Gateway Midtrans -->
                    <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/90 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Metode Pembayaran</span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-200/80 text-amber-900">Eksklusif Payment Gateway</span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-200/80 text-amber-900">Midtrans Gateway</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white p-2.5 rounded-xl border border-amber-200 shadow-2xs">
                            <div class="w-8 h-8 rounded-lg bg-stone-900 text-[#c59b27] flex items-center justify-center font-black text-xs shrink-0">
                                QRIS
                                SNAP
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-[#26211a]">Payment Gateway QRIS Instan</div>
                                <div class="text-[10px] text-[#786e60]">GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, dll.</div>
                                <div class="text-xs font-bold text-[#26211a]">Midtrans Payment Gateway</div>
                                <div class="text-[10px] text-[#786e60]">QRIS (GoPay, OVO, ShopeePay, Dana), BCA, Mandiri VA, dll.</div>
                            </div>
                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                        </div>
                        <p class="text-[10px] text-amber-900/90 leading-tight">
                            Pesanan online wajib lunas via Payment Gateway. Setelah pembayaran terverifikasi lunas, pesanan Anda otomatis langsung masuk ke sistem kasir dan dapur Fira Cake.
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
                            <span>Biaya Layanan Payment Gateway</span>
                            <span>Biaya Layanan Midtrans</span>
                            <span class="font-bold text-emerald-700">Gratis (Rp 0)</span>
                        </div>
                        <div class="pt-2 border-t border-[#e5dfd3] flex justify-between items-center">
                            <span class="font-bold text-xs text-[#26211a]">Total Tagihan Lunas:</span>
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
                    <button type="button" id="btnPayOnline" onclick="openPaymentGatewayModal()"
                    <button type="button" id="btnPayOnline" onclick="processMidtransCheckout()"
                        class="flex-1 py-2.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Bayar via Payment Gateway</span>
                        <span>Bayar Sekarang via Midtrans</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Simulasi / Tampilan Pembayaran Payment Gateway (QRIS) -->
        <div id="qrisPaymentModal" class="hidden fixed inset-0 z-50 bg-stone-950/85 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xs w-full p-5 text-center shadow-2xl border border-[#e5dfd3] space-y-3.5" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-2 border-b border-[#f0e8db]">
                    <div class="flex items-center gap-1.5 text-left">
                        <div class="w-6 h-6 rounded-lg bg-stone-900 text-[#c59b27] font-black text-[10px] flex items-center justify-center">
                            QR
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-[#26211a] leading-tight">Payment Gateway QRIS</h3>
                            <p class="text-[9px] text-[#786e60]">Fira Cake Bakery & Pastry</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePaymentGatewayModal()" class="text-stone-400 hover:text-stone-600 text-lg font-bold">
                        &times;
                    </button>
                </div>

                <p class="text-[10px] text-[#6b6252]">Pindai kode QRIS di bawah ini dengan aplikasi mobile banking atau e-wallet Anda:</p>

                <!-- QRIS Code Image Simulation -->
                <div class="p-3 bg-white rounded-2xl border-2 border-dashed border-[#c59b27] inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=FIRA-CAKE-ONLINE-ORDER-PAYMENT"
                        alt="QRIS Payment Gateway" class="w-40 h-40 mx-auto rounded-lg">
                </div>

                <div class="p-2.5 rounded-xl bg-[#faf7f0] border border-[#e8dfcf]">
                    <div class="text-[10px] text-[#786e60]">Total yang Harus Dibayar:</div>
                    <div id="qrisModalPayAmount" class="font-serif-classic text-lg font-black text-emerald-800">Rp 0</div>
                    <div class="text-[9px] text-[#8c8270] mt-0.5 font-medium">BCA &bull; Mandiri &bull; GoPay &bull; OVO &bull; DANA &bull; ShopeePay</div>
                </div>

                <div class="pt-1 space-y-2">
                    <button type="button" id="btnSimulatePaySuccess" onclick="processOnlineCheckout()"
                    <button type="button" id="btnSimulatePaySuccess" onclick="processFallbackMidtransSuccess()"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi Pembayaran Lunas</span>
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

        function incrementOnlineItem(id) {
            if (onlineCart[id]) {
                onlineCart[id].quantity += 1;
                saveCart();
                renderOnlineCart();
            }
        }

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

        function removeOnlineItem(id) {
            delete onlineCart[id];
            saveCart();
            renderOnlineCart();
        }

        function confirmClearOnlineCart() {
            if (confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang belanja?')) {
                onlineCart = {};
                saveCart();
                renderOnlineCart();
                closeCheckoutModal();
            }
        }

        function formatRupiah(num) {
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        function getOnlineCartTotal() {
            return Object.values(onlineCart).reduce((acc, item) => acc + (item.price * item.quantity), 0);
        }

        function getOnlineCartQty() {
            return Object.values(onlineCart).reduce((acc, item) => acc + item.quantity, 0);
        }

        // Render Tampilan Keranjang & Stepper
        function renderOnlineCart() {
            const totalQty = getOnlineCartQty();
            const totalPrice = getOnlineCartTotal();

            // Update Semua Stepper di Menu Card
            document.querySelectorAll('[id^="btnAdd-"]').forEach(btn => {
                const id = btn.id.replace('btnAdd-', '');
                const stepper = document.getElementById('stepper-' + id);
                const qtyDisp = document.getElementById('qtyDisplay-' + id);

                if (onlineCart[id] && onlineCart[id].quantity > 0) {
                    btn.classList.add('hidden');
                    if (stepper) stepper.classList.remove('hidden');
                    if (stepper) stepper.classList.add('flex');
                    if (qtyDisp) qtyDisp.innerText = onlineCart[id].quantity;
                } else {
                    btn.classList.remove('hidden');
                    if (stepper) stepper.classList.add('hidden');
                    if (stepper) stepper.classList.remove('flex');
                }
            });

            // Update Floating Bottom Bar
            const bar = document.getElementById('floatingCartBar');
            if (totalQty > 0) {
                document.getElementById('floatingTotalQty').innerText = totalQty;
                document.getElementById('floatingTotalPrice').innerText = formatRupiah(totalPrice);
                bar.classList.remove('hidden');
            } else {
                bar.classList.add('hidden');
            }

            // Update Isi Modal Checkout
            const listEl = document.getElementById('cartItemsList');
            const subtotalEl = document.getElementById('modalSubtotal');
            const totalPayEl = document.getElementById('modalTotalPay');

            if (subtotalEl) subtotalEl.innerText = formatRupiah(totalPrice);
            if (totalPayEl) totalPayEl.innerText = formatRupiah(totalPrice);

            if (listEl) {
                if (totalQty === 0) {
                    listEl.innerHTML = '<div class="text-center py-4 text-stone-400">Keranjang masih kosong</div>';
                } else {
                    listEl.innerHTML = Object.values(onlineCart).map(item => `
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

        // Modal Payment Gateway Controllers
        function openPaymentGatewayModal() {
        let currentOrderData = null;

        async function processMidtransCheckout() {
            const errEl = document.getElementById('checkoutError');
            errEl.classList.add('hidden');

            const name = (document.getElementById('custName').value || '').trim();
            const rawPhone = (document.getElementById('custPhone').value || '').trim();

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

            const total = getOnlineCartTotal();
            document.getElementById('qrisModalPayAmount').innerText = formatRupiah(total);
            document.getElementById('qrisPaymentModal').classList.remove('hidden');
        }
            const items = Object.values(onlineCart).map(item => ({
                id: item.id,
                quantity: item.quantity,
                notes: item.notes || null
            }));

        function closePaymentGatewayModal() {
            document.getElementById('qrisPaymentModal').classList.add('hidden');
        }
            if (items.length === 0) {
                errEl.innerText = 'Keranjang pesanan masih kosong. Silakan pilih menu terlebih dahulu.';
                errEl.classList.remove('hidden');
                return;
            }

        // Proses Checkout & Simpan Pesanan Online
        async function processOnlineCheckout() {
            const btn = document.getElementById('btnSimulatePaySuccess');
            const btn = document.getElementById('btnPayOnline');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memproses Pembayaran...</span>
                <span>Menyiapkan Midtrans...</span>
            `;

            const name = (document.getElementById('custName').value || '').trim();
            let rawPhone = (document.getElementById('custPhone').value || '').trim();
            if (rawPhone.startsWith('0')) {
                rawPhone = rawPhone.substring(1);
            let phone = rawPhone;
            if (phone.startsWith('0')) {
                phone = phone.substring(1);
            }
            const phone = '0' + rawPhone; // Format standar 08xxxx
            phone = '0' + phone;

            const address = (document.getElementById('custAddress').value || '').trim();
            const notes = (document.getElementById('custNotes').value || '').trim();

            const items = Object.values(onlineCart).map(item => ({
                id: item.id,
                quantity: item.quantity,
                notes: item.notes || null
            }));

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
                        payment_method: 'qris',
                        payment_method: 'midtrans',
                        items: items
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Kosongkan Keranjang Lokal
                    onlineCart = {};
                    saveCart();
                    // Arahkan ke Halaman Status Tracking Pesanan
                    window.location.href = data.data.redirect_url;
                    const snapToken = data.data.snap_token;
                    const redirectUrl = data.data.redirect_url;
                    const callbackUrl = data.data.success_callback_url;

                    if (snapToken && window.snap) {
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
                        // Jika window.snap tidak termuat atau sandbox fallback
                        currentOrderData = data.data;
                        document.getElementById('qrisModalPayAmount').innerText = formatRupiah(data.data.total_amount);
                        document.getElementById('qrisPaymentModal').classList.remove('hidden');
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    }
                } else {
                    alert(data.message || 'Gagal memproses pesanan online. Silakan coba kembali.');
                    errEl.innerText = data.message || 'Gagal memproses pesanan online.';
                    errEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = 'Konfirmasi Pembayaran Lunas';
                    btn.innerHTML = originalBtnHtml;
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan. Periksa koneksi internet Anda.');
                errEl.innerText = 'Terjadi kesalahan jaringan. Periksa koneksi internet Anda.';
                errEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Konfirmasi Pembayaran Lunas';
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

        function closePaymentGatewayModal() {
            document.getElementById('qrisPaymentModal').classList.add('hidden');
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

            document.querySelectorAll('.online-category-section').forEach(sec => {
                if (catId === 'all' || sec.dataset.categoryId === String(catId)) {
                    sec.classList.remove('hidden');
                } else {
                    sec.classList.add('hidden');
                }
            });
        }

        // Pencarian Menu
        function handleOnlineSearch() {
            const query = (document.getElementById('onlineSearchInput').value || '').trim().toLowerCase();
            const clearBtn = document.getElementById('clearSearchBtn');
            const emptyEl = document.getElementById('emptySearchState');

            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            let foundCount = 0;
            document.querySelectorAll('.online-product-card').forEach(card => {
                const name = card.dataset.productName || '';
                if (name.includes(query)) {
                    card.classList.remove('hidden');
                    foundCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Sembunyikan section kategori jika semua anaknya hidden
            document.querySelectorAll('.online-category-section').forEach(sec => {
                const visibleCards = sec.querySelectorAll('.online-product-card:not(.hidden)');
                if (visibleCards.length > 0) {
                    sec.classList.remove('hidden');
                } else {
                    sec.classList.add('hidden');
                }
            });

            if (foundCount === 0) {
                emptyEl.classList.remove('hidden');
            } else {
                emptyEl.classList.add('hidden');
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

