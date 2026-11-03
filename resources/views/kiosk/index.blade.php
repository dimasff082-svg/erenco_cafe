<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f5ef]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pemesanan Mandiri - Fira Cake</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }
        .font-serif-classic {
            font-family: 'Playfair Display', Georgia, serif;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1ebd9;
        }
        ::-webkit-scrollbar-thumb {
            background: #d4c8b0;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #c59b27;
        }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-[#2c261e] bg-[#f7f5ef] overflow-hidden text-xs">

    <!-- Top Compact Kiosk Header Bar -->
    <header class="h-13 bg-[#191714] text-[#f7f5f0] border-b border-[#2b261f] px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-md z-30">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-8 h-8 object-contain drop-shadow-sm">
            <div>
                <div class="flex items-center gap-1.5">
                    <h1 class="font-serif-classic text-sm sm:text-base font-extrabold tracking-wide text-[#f7f5f0]">Fira Cake</h1>
                    <span class="px-1.5 py-0.2 rounded-md text-[8.5px] font-black uppercase tracking-wider bg-[#c59b27] text-stone-950">
                        Self-Order
                    </span>
                </div>
                <p class="text-[9.5px] text-[#a49a88] leading-tight">Layar Pemesanan Mandiri Pelanggan</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Digital Clock Display -->
            <div class="hidden sm:flex flex-col text-right">
                <span id="kioskClock" class="font-mono text-xs font-bold text-[#e8dfcf]">--:--:--</span>
                <span id="kioskDate" class="text-[9px] text-[#8f8573]">Hari Ini</span>
            </div>

            <!-- Reset / Bersihkan Pesanan Button -->
            <button type="button" onclick="confirmResetKiosk()"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#2a2620] hover:bg-[#38332b] text-[#d6cfc2] hover:text-white border border-[#423c31] text-[10.5px] font-semibold transition active:scale-95">
                <svg class="w-3.5 h-3.5 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Reset</span>
            </button>
        </div>
    </header>

    <!-- Main Workspace (Catalog on Left, Compact Cart on Right) -->
    <div class="flex-1 flex overflow-hidden">

        <!-- Left Column: Menu Catalog (Scrollable) -->
        <main class="flex-1 flex flex-col overflow-hidden bg-[#f7f5ef] p-3 sm:p-4 space-y-2.5">

            <!-- Search Bar & Category Navigation (Compact) -->
            <div class="bg-white rounded-xl border border-[#e5dfd3] p-2 sm:p-2.5 shadow-2xs space-y-2 shrink-0">
                <div class="relative">
                    <input type="text" id="kioskSearchInput" oninput="handleKioskSearch()"
                        placeholder="Cari menu (misal: Roti, Brownies, Kopi)..."
                        class="w-full pl-3 pr-8 py-1.5 rounded-lg border border-[#dcd4c5] bg-[#faf7f0] text-xs text-[#26211a] font-medium focus:ring-1 focus:ring-[#c59b27] focus:bg-white focus:outline-none transition">
                    <button type="button" onclick="clearKioskSearch()" id="btnClearSearch"
                        class="hidden absolute right-2.5 top-1.5 text-[#8f8573] hover:text-[#26211a] text-sm font-bold">
                        &times;
                    </button>
                </div>

                <!-- Category Horizontal Scroll Bar -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5" id="kioskCategoryTabs">
                    <button type="button" onclick="selectKioskCategory('all')" id="kioskTab-all"
                        class="kiosk-cat-tab px-3 py-1 rounded-lg text-[10.5px] font-bold transition shrink-0 bg-[#c59b27] text-stone-950 font-black shadow-2xs">
                        Semua Menu
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" onclick="selectKioskCategory('cat-{{ $cat->id }}')" id="kioskTab-cat-{{ $cat->id }}"
                            class="kiosk-cat-tab px-3 py-1 rounded-lg text-[10.5px] font-bold transition shrink-0 bg-[#faf7f0] text-[#6b6252] hover:bg-[#ede7da] hover:text-[#2c261e] border border-[#e5dfd3]">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid (Compact, Smaller Cards) -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div id="kioskProductGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 sm:gap-2.5">
                    @foreach($categories as $category)
                        @foreach($category->products as $product)
                            <div class="kiosk-product-card bg-white rounded-xl border border-[#e5dfd3] p-2 sm:p-2.5 shadow-2xs hover:shadow-sm hover:border-[#c59b27] transition flex flex-col justify-between group"
                                data-id="{{ $product->id }}"
                                data-name="{{ strtolower($product->name) }}"
                                data-sku="{{ strtolower($product->sku) }}"
                                data-category="cat-{{ $category->id }}"
                                data-price="{{ (float)$product->selling_price }}">

                                <!-- Image / Photo (Compact) -->
                                <div class="relative w-full h-22 sm:h-26 rounded-lg bg-gradient-to-br from-[#faf7f0] to-[#f0e8db] overflow-hidden mb-1.5 flex items-center justify-center border border-[#e8dfcf]">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @else
                                        <div class="text-center p-1.5 text-stone-300">
                                            <svg class="w-6 h-6 mx-auto mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-[8.5px] font-bold text-[#8f8573] uppercase tracking-wider block truncate max-w-[90px]">{{ $category->name }}</span>
                                        </div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 bg-[#1f1c18]/80 text-[#eac36c] backdrop-blur-xs text-[8.5px] font-bold px-1.5 py-0.5 rounded font-mono">
                                        {{ $product->sku }}
                                    </span>
                                </div>

                                <!-- Information (Compact text) -->
                                <div class="flex-1 flex flex-col">
                                    <span class="text-[8.5px] font-bold text-[#8f8573] uppercase tracking-wider block truncate">
                                        {{ $category->name }}
                                    </span>
                                    <h3 class="text-[11px] sm:text-xs font-bold text-[#26211a] line-clamp-1 mt-0.5 leading-snug group-hover:text-[#a87f1c] transition">
                                        {{ $product->name }}
                                    </h3>
                                    @if($product->description)
                                        <p class="text-[9.5px] text-[#786e60] line-clamp-1 mt-0.5 leading-tight">
                                            {{ $product->description }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Price & Compact Add Button -->
                                <div class="mt-2 pt-1.5 border-t border-[#f0e8db] flex items-center justify-between gap-1">
                                    <div>
                                        <span class="text-[8.5px] text-[#8f8573] block leading-none">Harga</span>
                                        <span class="text-[11px] sm:text-xs font-black text-[#26211a]">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <button type="button"
                                        onclick="addKioskItem({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$product->selling_price }})"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-[10px] sm:text-[10.5px] shadow-2xs transition"
                                        title="Pesan Menu Ini">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span>+ Pesan</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                <!-- Empty Search Fallback -->
                <div id="kioskNoMatch" class="hidden bg-white rounded-2xl p-8 text-center border border-[#e5dfd3] shadow-xs my-4">
                    <svg class="w-8 h-8 mx-auto text-stone-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-xs font-bold text-[#26211a]">Menu tidak ditemukan</h3>
                    <p class="text-[10px] text-[#8f8573] mt-0.5 max-w-xs mx-auto">Menu yang Anda cari tidak tersedia. Coba gunakan kata kunci lain atau pilih kategori.</p>
                </div>
            </div>
        </main>

        <!-- Right Column: Compact Cart & Checkout Form -->
        <aside class="w-72 sm:w-80 lg:w-[320px] bg-white border-l border-[#e5dfd3] flex flex-col shadow-xl shrink-0 z-20">

            <!-- Cart Header (Compact) -->
            <div class="px-3.5 py-2.5 bg-[#191714] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-[#c59b27] text-stone-950 flex items-center justify-center font-bold text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xs font-extrabold tracking-wide text-[#f7f5f0]">Keranjang Pesanan</h2>
                        <span class="text-[9px] text-[#c59b27] font-semibold uppercase tracking-wider block leading-none">Self-Order Kiosk</span>
                    </div>
                </div>
                <span id="kioskCartItemCount" class="px-2 py-0.5 rounded-full text-[10.5px] font-black bg-[#c59b27] text-stone-950">
                    0 Item
                </span>
            </div>

            <!-- Customer Identity Form (Compact) -->
            <div class="p-2.5 bg-[#faf7f0] border-b border-[#e5dfd3] space-y-2 shrink-0">
                <div>
                    <label class="block text-[9.5px] font-bold uppercase tracking-wider text-[#4d4436] mb-0.5">
                        Nama Pelanggan <span class="text-rose-600 font-black">*</span>
                    </label>
                    <input type="text" id="kioskCustomerName" placeholder="Ketik nama Anda untuk panggilan..."
                        class="w-full px-2.5 py-1.5 rounded-lg border border-[#dcd4c5] bg-white text-xs font-semibold text-[#2c261e] focus:ring-1 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[9px] font-bold uppercase tracking-wider text-[#6b6252] mb-0.5">
                            No. HP Pelanggan
                        </label>
                        <input type="tel" id="kioskCustomerPhone" placeholder="08xxxxxxxxxx"
                            class="w-full px-2 py-1 rounded-lg border border-[#dcd4c5] bg-white text-[11px] text-[#2c261e] focus:ring-1 focus:ring-[#c59b27] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold uppercase tracking-wider text-[#6b6252] mb-0.5">
                            Catatan Pesanan
                        </label>
                        <input type="text" id="kioskOrderNotes" placeholder="Kurang manis, dll"
                            class="w-full px-2 py-1 rounded-lg border border-[#dcd4c5] bg-white text-[11px] text-[#2c261e] focus:ring-1 focus:ring-[#c59b27] focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                <div id="kioskEmptyCart" class="py-8 text-center text-[#8f8573] space-y-1.5">
                    <svg class="w-8 h-8 mx-auto text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h4 class="text-xs font-bold text-[#26211a]">Keranjang Masih Kosong</h4>
                    <p class="text-[10px] text-[#8f8573] max-w-xs mx-auto">Sentuh tombol "+ Pesan" pada menu untuk menambahkan.</p>
                </div>

                <div id="kioskCartList" class="space-y-2"></div>
            </div>

            <!-- Payment Method & Checkout Section (Bottom Pinned, Compact) -->
            <div class="p-2.5 bg-[#faf7f0] border-t border-[#e5dfd3] space-y-2 shrink-0">

                <!-- Totals -->
                <div class="bg-white rounded-xl border border-[#e5dfd3] p-2 flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#8f8573] block">Total Tagihan</span>
                        <span class="text-[9.5px] text-[#6b6252]">Termasuk pajak & layanan</span>
                    </div>
                    <span id="kioskTotalDisplay" class="text-base font-black text-[#a87f1c]">Rp 0</span>
                </div>

                <!-- Payment Method Select -->
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-wider text-[#6b6252] mb-1">
                        Pilih Metode Pembayaran
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <label class="cursor-pointer">
                            <input type="radio" name="kiosk_payment" value="cash" checked onchange="toggleKioskPaymentInfo()" class="peer sr-only">
                            <div class="p-1.5 rounded-lg border border-[#dcd4c5] bg-white text-center peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-2xs transition">
                                <span class="text-[10.5px] font-black block">Tunai</span>
                                <span class="text-[8.5px] text-[#6b6252] peer-checked:text-stone-800 block">Bayar di Kasir</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="kiosk_payment" value="qris" onchange="toggleKioskPaymentInfo()" class="peer sr-only">
                            <div class="p-1.5 rounded-lg border border-[#dcd4c5] bg-white text-center peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-2xs transition">
                                <span class="text-[10.5px] font-black block">QRIS</span>
                                <span class="text-[8.5px] text-[#6b6252] peer-checked:text-stone-800 block">Gateway Instan</span>
                            </div>
                        </label>
                    </div>

                    <div id="paymentInfoNote" class="mt-1.5 p-1.5 rounded-lg bg-amber-50 border border-amber-200 text-[9.5px] text-amber-900 leading-tight">
                        <strong>Tunai:</strong> Ambil struk pesanan & bayar di kasir. Pesanan akan dimasak dapur setelah lunas di kasir.
                    </div>
                </div>

                <!-- Alert Error Box -->
                <div id="kioskAlert" class="hidden p-2 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-[10.5px] font-medium"></div>

                <!-- Checkout Action Button -->
                <button type="button" id="btnKioskCheckout" onclick="submitKioskOrder()"
                    class="w-full py-2.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-[0.98] text-stone-950 font-black text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Konfirmasi & Buat Pesanan</span>
                </button>
            </div>
        </aside>
    </div>

    <!-- QRIS Payment Gateway Simulation Modal -->
    <div id="qrisModal" class="hidden fixed inset-0 z-50 bg-stone-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xs w-full p-4 text-center shadow-xl border border-[#e5dfd3] space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-[#f0e8db]">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <h3 class="text-xs font-extrabold text-[#26211a]">QRIS Payment Gateway</h3>
                </div>
                <button type="button" onclick="closeQrisModal()" class="text-[#8f8573] hover:text-[#26211a] text-lg font-bold">
                    &times;
                </button>
            </div>

            <div>
                <p class="text-[10px] text-[#6b6252]">Pindai QRIS di bawah ini dengan mobile banking atau e-wallet:</p>
                <div class="my-3 p-3 bg-white rounded-xl border border-dashed border-[#c59b27] inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=FIRA-CAKE-QRIS-PAYMENT"
                        alt="QRIS Code" class="w-36 h-36 mx-auto rounded-lg">
                </div>
                <div class="text-[11px] font-bold text-[#26211a]">
                    Total Tagihan: <span id="qrisModalTotal" class="text-emerald-700 font-black text-sm">Rp 0</span>
                </div>
                <p class="text-[9px] text-[#8f8573] mt-0.5">Dukung GoPay, OVO, Dana, LinkAja, BCA, Mandiri, dll.</p>
            </div>

            <div class="pt-1 flex flex-col gap-1.5">
                <button type="button" id="btnConfirmQrisPaid" onclick="executeKioskOrderSubmission('qris')"
                    class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs shadow-sm transition">
                    ✓ Saya Sudah Membayar via QRIS
                </button>
                <button type="button" onclick="closeQrisModal()"
                    class="w-full py-1.5 rounded-lg bg-[#faf7f0] hover:bg-[#ede7da] text-[#6b6252] font-semibold text-[10.5px] transition">
                    Batal / Ganti Metode
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript Application Logic -->
    <script>
        let kioskCart = {};

        // Digital Clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            const clockEl = document.getElementById('kioskClock');
            const dateEl = document.getElementById('kioskDate');
            if (clockEl) clockEl.innerText = timeStr;
            if (dateEl) dateEl.innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Category Filtering
        function selectKioskCategory(catId) {
            document.querySelectorAll('.kiosk-cat-tab').forEach(tab => {
                tab.className = "kiosk-cat-tab px-3 py-1 rounded-lg text-[10.5px] font-bold transition shrink-0 bg-[#faf7f0] text-[#6b6252] hover:bg-[#ede7da] hover:text-[#2c261e] border border-[#e5dfd3]";
            });
            const activeTab = document.getElementById(`kioskTab-${catId}`);
            if (activeTab) {
                activeTab.className = "kiosk-cat-tab px-3 py-1 rounded-lg text-[10.5px] font-bold transition shrink-0 bg-[#c59b27] text-stone-950 font-black shadow-2xs";
            }

            const cards = document.querySelectorAll('.kiosk-product-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const matchCat = (catId === 'all' || card.getAttribute('data-category') === catId);
                const query = (document.getElementById('kioskSearchInput').value || '').trim().toLowerCase();
                const matchSearch = (!query || card.getAttribute('data-name').includes(query) || card.getAttribute('data-sku').includes(query));

                if (matchCat && matchSearch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noMatch = document.getElementById('kioskNoMatch');
            if (visibleCount === 0) {
                noMatch.classList.remove('hidden');
            } else {
                noMatch.classList.add('hidden');
            }
        }

        // Live Search
        function handleKioskSearch() {
            const query = (document.getElementById('kioskSearchInput').value || '').trim().toLowerCase();
            const btnClear = document.getElementById('btnClearSearch');
            if (query) {
                btnClear.classList.remove('hidden');
            } else {
                btnClear.classList.add('hidden');
            }

            const activeTab = document.querySelector('.kiosk-cat-tab.bg-\\[\\#c59b27\\]');
            const catId = activeTab ? activeTab.id.replace('kioskTab-', '') : 'all';

            const cards = document.querySelectorAll('.kiosk-product-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const matchCat = (catId === 'all' || card.getAttribute('data-category') === catId);
                const matchSearch = (!query || card.getAttribute('data-name').includes(query) || card.getAttribute('data-sku').includes(query));

                if (matchCat && matchSearch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noMatch = document.getElementById('kioskNoMatch');
            if (visibleCount === 0) {
                noMatch.classList.remove('hidden');
            } else {
                noMatch.classList.add('hidden');
            }
        }

        function clearKioskSearch() {
            document.getElementById('kioskSearchInput').value = '';
            handleKioskSearch();
        }

        // Add Menu to Cart
        function addKioskItem(id, name, price) {
            if (kioskCart[id]) {
                kioskCart[id].quantity += 1;
            } else {
                kioskCart[id] = {
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    notes: ''
                };
            }
            renderKioskCart();
        }

        function updateKioskItemQty(id, delta) {
            if (!kioskCart[id]) return;
            kioskCart[id].quantity += delta;
            if (kioskCart[id].quantity <= 0) {
                delete kioskCart[id];
            }
            renderKioskCart();
        }

        function removeKioskItem(id) {
            if (kioskCart[id]) {
                delete kioskCart[id];
                renderKioskCart();
            }
        }

        function updateKioskItemNotes(id, text) {
            if (kioskCart[id]) {
                kioskCart[id].notes = text;
            }
        }

        function getKioskTotal() {
            return Object.values(kioskCart).reduce((acc, it) => acc + (it.price * it.quantity), 0);
        }

        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function renderKioskCart() {
            const listContainer = document.getElementById('kioskCartList');
            const emptyEl = document.getElementById('kioskEmptyCart');
            const countBadge = document.getElementById('kioskCartItemCount');
            const totalDisplay = document.getElementById('kioskTotalDisplay');

            const items = Object.values(kioskCart);
            const totalQty = items.reduce((acc, it) => acc + it.quantity, 0);
            const totalAmt = getKioskTotal();

            countBadge.innerText = totalQty + ' Item';
            totalDisplay.innerText = formatRupiah(totalAmt);

            if (items.length === 0) {
                listContainer.innerHTML = '';
                emptyEl.classList.remove('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            let html = '';

            items.forEach(it => {
                const lineTotal = it.price * it.quantity;
                html += `
                    <div class="p-2 bg-[#faf7f0] rounded-xl border border-[#e5dfd3] space-y-1.5">
                        <div class="flex items-start justify-between gap-1.5">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[11px] font-bold text-[#26211a] truncate">${it.name}</h4>
                                <span class="text-[9.5px] text-[#8f8573]">${formatRupiah(it.price)} &times; ${it.quantity}</span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] font-black text-[#26211a] block">${formatRupiah(lineTotal)}</span>
                                <button type="button" onclick="removeKioskItem(${it.id})" class="text-[9px] text-rose-600 hover:text-rose-800 font-bold">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-1.5 pt-1 border-t border-[#eee7da]">
                            <div class="flex items-center gap-1 bg-white border border-[#dcd4c5] rounded-md p-0.5 shadow-2xs shrink-0">
                                <button type="button" onclick="updateKioskItemQty(${it.id}, -1)"
                                    class="w-5 h-5 rounded bg-[#faf7f0] hover:bg-[#ede7da] text-[#2c261e] font-black text-[10px] flex items-center justify-center">
                                    -
                                </button>
                                <span class="w-5 text-center text-[10.5px] font-black text-[#26211a]">${it.quantity}</span>
                                <button type="button" onclick="updateKioskItemQty(${it.id}, 1)"
                                    class="w-5 h-5 rounded bg-[#c59b27] hover:bg-[#a87f1c] text-stone-950 font-black text-[10px] flex items-center justify-center">
                                    +
                                </button>
                            </div>

                            <input type="text" value="${it.notes || ''}" onchange="updateKioskItemNotes(${it.id}, this.value)"
                                placeholder="Catatan..."
                                class="flex-1 px-2 py-0.5 rounded border border-[#dcd4c5] bg-white text-[9.5px] text-[#2c261e] focus:ring-1 focus:ring-[#c59b27] focus:outline-none">
                        </div>
                    </div>
                `;
            });

            listContainer.innerHTML = html;
        }

        function toggleKioskPaymentInfo() {
            const method = document.querySelector('input[name="kiosk_payment"]:checked')?.value || 'cash';
            const noteEl = document.getElementById('paymentInfoNote');
            if (method === 'cash') {
                noteEl.innerHTML = '<strong>Tunai:</strong> Ambil struk pesanan & bayar di kasir. Pesanan akan dimasak dapur setelah lunas di kasir.';
            } else {
                noteEl.innerHTML = '<strong>QRIS:</strong> Bayar instan di layar ini. Pesanan langsung lunas & langsung masuk dapur.';
            }
        }

        function confirmResetKiosk() {
            if (Object.keys(kioskCart).length > 0) {
                if (confirm('Apakah Anda ingin membatalkan dan membersihkan seluruh keranjang pesanan?')) {
                    resetKioskState();
                }
            } else {
                resetKioskState();
            }
        }

        function resetKioskState() {
            kioskCart = {};
            document.getElementById('kioskCustomerName').value = '';
            document.getElementById('kioskCustomerPhone').value = '';
            document.getElementById('kioskOrderNotes').value = '';
            document.querySelector('input[name="kiosk_payment"][value="cash"]').checked = true;
            toggleKioskPaymentInfo();
            renderKioskCart();
            clearKioskSearch();
            selectKioskCategory('all');
            hideKioskAlert();
        }

        function showKioskAlert(msg) {
            const alertEl = document.getElementById('kioskAlert');
            alertEl.innerText = msg;
            alertEl.classList.remove('hidden');
        }

        function hideKioskAlert() {
            document.getElementById('kioskAlert').classList.add('hidden');
        }

        function submitKioskOrder() {
            hideKioskAlert();
            const items = Object.values(kioskCart);
            if (items.length === 0) {
                showKioskAlert('Keranjang pesanan masih kosong. Sentuh tombol "+ Pesan" pada menu pilihan Anda.');
                return;
            }

            const name = (document.getElementById('kioskCustomerName').value || '').trim();
            if (!name) {
                showKioskAlert('Mohon isi Nama Pelanggan untuk pemanggilan pesanan Anda.');
                document.getElementById('kioskCustomerName').focus();
                return;
            }

            const method = document.querySelector('input[name="kiosk_payment"]:checked')?.value || 'cash';

            if (method === 'qris') {
                // Tampilkan Modal QRIS Payment Gateway
                document.getElementById('qrisModalTotal').innerText = formatRupiah(getKioskTotal());
                document.getElementById('qrisModal').classList.remove('hidden');
            } else {
                // Langsung proses Tunai
                executeKioskOrderSubmission('cash');
            }
        }

        function closeQrisModal() {
            document.getElementById('qrisModal').classList.add('hidden');
        }

        async function executeKioskOrderSubmission(method) {
            closeQrisModal();
            const btn = document.getElementById('btnKioskCheckout');
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memproses...</span>
            `;

            const name = (document.getElementById('kioskCustomerName').value || '').trim();
            const phone = (document.getElementById('kioskCustomerPhone').value || '').trim();
            const notes = (document.getElementById('kioskOrderNotes').value || '').trim();
            const items = Object.values(kioskCart);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch("{{ route('kiosk.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        customer_name: name,
                        customer_phone: phone,
                        notes: notes,
                        payment_method: method,
                        items: items.map(it => ({
                            id: it.id,
                            quantity: it.quantity,
                            notes: it.notes
                        }))
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                }

                // Sukses! Arahkan ke halaman struk kiosk
                window.location.href = data.receipt_url;

            } catch (err) {
                showKioskAlert(err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
