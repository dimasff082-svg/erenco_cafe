@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-8.5rem)] flex flex-col lg:flex-row gap-5 -m-4 sm:-m-6 p-4 sm:p-6">
    <!-- LEFT PANEL: Product Catalogue & Fast Search (50% WIDTH) -->
    <div class="w-full lg:w-1/2 flex flex-col bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] overflow-hidden">
        <!-- Top Toolbar: Search & Category Filter -->
        <div class="p-4 border-b border-[#f0ebd9] space-y-3 bg-[#faf7f0]">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#a89d8d]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="pos-search-input" autofocus
                    placeholder="Cari barang berdasarkan nama produk atau kode SKU..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl pl-10 pr-4 py-2.5 text-xs font-medium text-[#262017] placeholder-[#a89d8d] focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-2xs transition">
            </div>

            <!-- Category Pills Filter -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold no-scrollbar">
                <button type="button" onclick="filterCategory(null)" id="cat-pill-all"
                    class="category-pill px-3.5 py-1.5 rounded-xl transition whitespace-nowrap bg-[#c59b27] text-stone-950 font-bold border border-[#dfb15b]/40 shadow-xs">
                    Semua Kategori
                </button>
                @foreach($categories as $cat)
                    <button type="button" onclick="filterCategory({{ $cat->id }})" id="cat-pill-{{ $cat->id }}"
                        class="category-pill px-3.5 py-1.5 rounded-xl transition whitespace-nowrap bg-white border border-[#dcd4c5] text-[#4d4437] hover:bg-[#faf7f0]">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid List: 2 Kolom Produk Berdampingan (Bagi Dua Kolom) -->
        <div class="flex-1 p-3.5 sm:p-4 overflow-y-auto">
            <div id="product-grid" class="grid grid-cols-2 gap-3 content-start">
                @foreach($products as $prod)
                    <div class="product-card group relative bg-white border border-[#e5dfd3] rounded-2xl p-3 sm:p-3.5 flex flex-col justify-between hover:border-[#c59b27] hover:shadow-[0_8px_25px_-5px_rgba(40,30,15,0.1)] transition-all duration-200 cursor-pointer shadow-[0_2px_8px_-2px_rgba(40,30,15,0.04)] h-[190px] w-full"
                        data-id="{{ $prod->id }}"
                        data-name="{{ $prod->name }}"
                        data-sku="{{ $prod->sku }}"
                        data-price="{{ $prod->selling_price }}"
                        data-stock="{{ $prod->stock }}"
                        data-unit="{{ $prod->unit }}"
                        data-category="{{ $prod->category_id }}"
                        onclick="addToCart({{ $prod->id }})">

                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="font-mono text-[10px] sm:text-[11px] font-bold text-[#8a681c] bg-amber-50/80 px-1.5 py-0.5 rounded border border-amber-200/60 group-hover:text-amber-800 transition truncate max-w-[85px]">
                                    {{ $prod->sku }}
                                </span>
                                @if($prod->stock <= 0)
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 shrink-0">
                                        Habis
                                    </span>
                                @elseif($prod->isLowStock())
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                                        Sisa {{ $prod->stock }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-semibold text-[#786e60] bg-[#faf7f0] border border-[#e5dfd3] px-1.5 py-0.5 rounded-full shrink-0">
                                        Stok: {{ $prod->stock }}
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-serif-classic font-bold text-xs sm:text-sm text-[#262017] line-clamp-2 group-hover:text-[#8a681c] transition leading-snug">
                                {{ $prod->name }}
                            </h4>
                            <p class="text-[10px] text-[#786e60] font-sans mt-0.5 truncate">
                                {{ $prod->category->name ?? 'Kategori Umum' }} &bull; {{ $prod->unit }}
                            </p>
                        </div>

                        <div class="mt-2 pt-2 border-t border-[#f0ebd9] flex items-center justify-between">
                            <div>
                                <span class="text-[8px] sm:text-[9px] text-[#786e60] block font-sans uppercase tracking-wider font-semibold">Harga</span>
                                <span class="text-xs sm:text-sm xl:text-base font-serif-classic font-bold text-[#1b4332]">
                                    Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                </span>
                            </div>
                            <button type="button" aria-label="Tambah {{ $prod->name }} ke keranjang" class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-[#8a681c] border border-amber-200/80 group-hover:bg-[#c59b27] group-hover:text-stone-950 group-hover:border-[#c59b27] flex items-center justify-center transition shadow-2xs group-hover:scale-105 active:scale-95 shrink-0">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty state when search yields no products -->
            <div id="no-products-msg" class="hidden py-16 text-center text-[#998f80]">
                <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-center text-[#b8a994]">
                    <i class="fa-solid fa-box-open text-lg"></i>
                </div>
                <p class="font-serif-classic font-bold text-sm text-[#262017]">Barang tidak ditemukan.</p>
                <p class="text-xs text-[#786e60] mt-1 font-sans">Periksa kata kunci pencarian atau pilih kategori lain.</p>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Interactive Cart & Checkout Engine (50% WIDTH EQUAL SPLIT) -->
    <div class="w-full lg:w-1/2 flex flex-col bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.06)] overflow-hidden h-full">
        <!-- Cart Header -->
        <div class="p-3.5 border-b border-[#f0ebd9] flex items-center justify-between bg-[#faf7f0] shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#8a681c] border border-amber-200/80 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-serif-classic font-bold text-base text-[#262017]">Keranjang Belanja</h3>
                        <span id="cart-badge-count" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#c59b27] text-stone-950">0 item</span>
                    </div>
                    <p class="text-[10px] text-[#786e60]">Daftar pesanan pelanggan saat ini</p>
                </div>
            </div>
            <button type="button" onclick="clearCart()" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition px-2.5 py-1 rounded-lg hover:bg-rose-50 border border-transparent hover:border-rose-200">
                Kosongkan
            </button>
        </div>

        <!-- Cart Items List (Scrollable, never pushes footer off screen) -->
        <div class="flex-1 min-h-[120px] p-3.5 overflow-y-auto space-y-2.5">
            <div id="cart-empty-state" class="py-12 text-center text-[#998f80]">
                <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-center text-[#b8a994]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <p class="text-sm font-serif-classic font-bold text-[#362f25]">Keranjang belanja masih kosong</p>
                <p class="text-xs text-[#8a7f70] mt-0.5">Klik item barang pada katalog di sebelah kiri untuk menambahkan pesanan.</p>
            </div>
            <div id="cart-items-container" class="space-y-2.5"></div>
        </div>

        <!-- Payment & Total Calculation Section (Fixed at bottom with internal scrolling if needed) -->
        <div class="p-4 border-t border-[#e5dfd3] bg-[#faf7f0] space-y-3 shrink-0 overflow-y-auto max-h-[55%]">
            <!-- Totals & Discount Input -->
            <div class="space-y-1.5 text-xs bg-white p-3 rounded-xl border border-[#e5dfd3]">
                <div class="flex justify-between text-[#786e60] font-medium">
                    <span>Subtotal Barang (<span id="cart-count">0</span> item)</span>
                    <span id="cart-subtotal" class="font-bold text-[#262017]">Rp 0</span>
                </div>

                <!-- Fitur Diskon Transaksi -->
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-[#f0ebd9]">
                    <span class="text-[#786e60] font-medium flex items-center gap-1">
                        <i class="fa-solid fa-tags text-[10px] text-amber-600"></i> Diskon (Rp)
                    </span>
                    <div class="w-32">
                        <input type="number" id="cart-discount" min="0" step="1000" placeholder="0" value="0"
                            oninput="handleDiscountChange()"
                            class="w-full text-right bg-[#fcfbf9] border border-[#dcd4c5] rounded-lg px-2 py-1 text-xs font-bold text-[#8a681c] focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>

                <div class="flex justify-between items-baseline pt-1.5 border-t border-[#e8e2d4]">
                    <span class="text-xs font-bold text-[#786e60] uppercase tracking-wider">Total Tagihan</span>
                    <span id="cart-total" class="text-xl font-serif-classic font-bold text-[#1b4332]">Rp 0</span>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#786e60] mb-1">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <label class="border-2 border-[#dcd4c5] bg-white rounded-xl p-2 text-center cursor-pointer hover:border-[#c59b27] has-[:checked]:border-[#c59b27] has-[:checked]:bg-[#fdf9f0] has-[:checked]:text-[#8a681c] font-bold transition shadow-2xs">
                        <input type="radio" name="payment_method" value="cash" checked onchange="handlePaymentMethodChange()" class="hidden">
                        <span>Tunai</span>
                    </label>
                    <label class="border-2 border-[#dcd4c5] bg-white rounded-xl p-2 text-center cursor-pointer hover:border-[#c59b27] has-[:checked]:border-[#c59b27] has-[:checked]:bg-[#fdf9f0] has-[:checked]:text-[#8a681c] font-bold transition shadow-2xs">
                        <input type="radio" name="payment_method" value="transfer" onchange="handlePaymentMethodChange()" class="hidden">
                        <span>Transfer</span>
                    </label>
                    <label class="border-2 border-[#dcd4c5] bg-white rounded-xl p-2 text-center cursor-pointer hover:border-[#c59b27] has-[:checked]:border-[#c59b27] has-[:checked]:bg-[#fdf9f0] has-[:checked]:text-[#8a681c] font-bold transition shadow-2xs">
                        <input type="radio" name="payment_method" value="qris" onchange="handlePaymentMethodChange()" class="hidden">
                        <span>QRIS</span>
                    </label>
                </div>
            </div>

            <!-- Cash Input Section -->
            <div id="cash-input-section" class="space-y-2 bg-white p-3 rounded-xl border border-[#e5dfd3]">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-bold uppercase tracking-wider text-[#786e60]">Uang Diterima (Rp)</span>
                    <button type="button" onclick="setExactAmount()" class="text-emerald-700 hover:text-emerald-900 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Uang Pas
                    </button>
                </div>
                <div class="relative">
                    <input type="number" id="cash-tendered" min="0" step="500" placeholder="0" oninput="calculateChange()"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-base font-black text-[#262017] focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <!-- Quick Presets -->
                <div class="grid grid-cols-4 gap-1.5 pt-0.5">
                    <button type="button" onclick="addPresetCash(10000)" class="py-1 rounded-lg bg-[#faf7f0] border border-[#dcd4c5] hover:bg-[#c59b27] hover:text-stone-950 text-[11px] font-bold text-[#5c5346] transition">10k</button>
                    <button type="button" onclick="addPresetCash(20000)" class="py-1 rounded-lg bg-[#faf7f0] border border-[#dcd4c5] hover:bg-[#c59b27] hover:text-stone-950 text-[11px] font-bold text-[#5c5346] transition">20k</button>
                    <button type="button" onclick="addPresetCash(50000)" class="py-1 rounded-lg bg-[#faf7f0] border border-[#dcd4c5] hover:bg-[#c59b27] hover:text-stone-950 text-[11px] font-bold text-[#5c5346] transition">50k</button>
                    <button type="button" onclick="addPresetCash(100000)" class="py-1 rounded-lg bg-[#faf7f0] border border-[#dcd4c5] hover:bg-[#c59b27] hover:text-stone-950 text-[11px] font-bold text-[#5c5346] transition">100k</button>
                </div>

                <!-- Change Calculation Result -->
                <div class="p-2.5 rounded-lg bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-between text-xs">
                    <span class="text-[#786e60] font-medium">Uang Kembalian:</span>
                    <span id="cash-change" class="text-sm font-black font-serif-classic text-[#1b4332]">Rp 0</span>
                </div>
                <p id="cash-error" class="hidden text-xs font-bold text-rose-600"></p>
            </div>

            <!-- Notes Section -->
            <div>
                <input type="text" id="cart-notes" placeholder="Catatan transaksi / Nomor meja (opsional)..."
                    class="w-full bg-white border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] placeholder-[#a89d8d] focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Submit Checkout Button -->
            <button type="button" id="btn-checkout" onclick="processCheckout()" disabled
                class="w-full py-3 px-4 rounded-xl bg-[#1b4332] hover:bg-[#2d6a4f] disabled:bg-stone-200 disabled:text-stone-400 text-emerald-100 font-serif-classic font-bold text-sm shadow-md transition transform active:scale-98 flex items-center justify-center gap-2 border border-emerald-500/30 disabled:border-transparent">
                <svg id="checkout-spinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-emerald-100" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="checkout-btn-text">Selesaikan Pembayaran & Cetak Struk</span>
            </button>
        </div>
    </div>
</div>

<script>
    // In-memory catalog from server
    const productsData = @json($products->keyBy('id'));
    let cart = {}; // { [id]: { product, quantity } }
    let selectedCategoryId = null;

    // Fast Client-side Search & Filter
    const searchInput = document.getElementById('pos-search-input');
    searchInput.addEventListener('input', function() {
        filterProducts();
    });

    // Barcode scanner & Enter key support in search input
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value.trim().toLowerCase();
            if (!query) {
                processCheckout();
                return;
            }

            // Check if there is an exact SKU match
            let matchedCard = null;
            const visibleCards = document.querySelectorAll('.product-card:not(.hidden)');

            for (const card of visibleCards) {
                const sku = card.getAttribute('data-sku').toLowerCase();
                if (sku === query) {
                    matchedCard = card;
                    break;
                }
            }

            // Fallback to the single visible card if only 1 matches
            if (!matchedCard && visibleCards.length === 1) {
                matchedCard = visibleCards[0];
            }

            if (matchedCard) {
                const prodId = parseInt(matchedCard.getAttribute('data-id'));
                if (prodId) {
                    addToCart(prodId);
                    this.value = '';
                    filterProducts();
                }
            }
        }
    });

    function filterCategory(catId) {
        selectedCategoryId = catId;
        document.querySelectorAll('.category-pill').forEach(el => {
            el.classList.remove('bg-[#c59b27]', 'text-stone-950', 'font-bold', 'border-[#dfb15b]/40', 'shadow-xs');
            el.classList.add('bg-white', 'border', 'border-[#dcd4c5]', 'text-[#4d4437]');
        });

        const activeId = catId ? 'cat-pill-' + catId : 'cat-pill-all';
        const activeBtn = document.getElementById(activeId);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'border-[#dcd4c5]', 'text-[#4d4437]');
            activeBtn.classList.add('bg-[#c59b27]', 'text-stone-950', 'font-bold', 'border-[#dfb15b]/40', 'shadow-xs');
        }

        filterProducts();
    }

    function filterProducts() {
        const query = searchInput.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const sku = card.getAttribute('data-sku').toLowerCase();
            const cat = card.getAttribute('data-category');

            const matchesQuery = query === '' || name.includes(query) || sku.includes(query);
            const matchesCategory = selectedCategoryId === null || cat == selectedCategoryId;

            if (matchesQuery && matchesCategory) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const noMsg = document.getElementById('no-products-msg');
        if (visibleCount === 0) {
            noMsg.classList.remove('hidden');
        } else {
            noMsg.classList.add('hidden');
        }
    }

    // Cart Management
    function addToCart(productId) {
        const product = productsData[productId];
        if (!product) return;

        if (product.stock <= 0) {
            alert("Maaf, stok barang '" + product.name + "' sudah habis!");
            return;
        }

        if (cart[productId]) {
            if (cart[productId].quantity + 1 > product.stock) {
                alert("Jumlah melebihi stok yang tersedia (" + product.stock + " " + product.unit + ").");
                return;
            }
            cart[productId].quantity += 1;
        } else {
            cart[productId] = {
                product: product,
                quantity: 1
            };
        }

        renderCart();
    }

    function updateCartQty(productId, newQty) {
        const item = cart[productId];
        if (!item) return;

        newQty = parseInt(newQty);
        if (isNaN(newQty) || newQty <= 0) {
            removeFromCart(productId);
            return;
        }

        if (newQty > item.product.stock) {
            alert("Jumlah melebihi stok yang tersedia (" + item.product.stock + " " + item.product.unit + ").");
            item.quantity = item.product.stock;
        } else {
            item.quantity = newQty;
        }

        renderCart();
    }

    function removeFromCart(productId) {
        delete cart[productId];
        renderCart();
    }

    function clearCart(skipConfirm) {
        if (Object.keys(cart).length === 0) return;
        if (!skipConfirm && typeof window.showConfirmDialog === 'function') {
            window.showConfirmDialog({
                title: 'Kosongkan Keranjang POS',
                message: 'Apakah Anda yakin ingin mengosongkan seluruh item dalam keranjang belanja kasir?',
                confirmText: 'Ya, Kosongkan',
                cancelText: 'Batal',
                type: 'danger',
                onConfirm: function() {
                    clearCart(true);
                }
            });
            return;
        }
        cart = {};
        if (document.getElementById('cart-discount')) {
            document.getElementById('cart-discount').value = 0;
        }
        if (document.getElementById('cart-notes')) {
            document.getElementById('cart-notes').value = '';
        }
        if (document.getElementById('cash-tendered')) {
            document.getElementById('cash-tendered').value = '';
        }
        renderCart();
    }

    function formatRp(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function getDiscountAmount() {
        const input = document.getElementById('cart-discount');
        const val = parseFloat(input ? input.value : 0) || 0;
        return Math.max(0, val);
    }

    function getSubtotalAmount() {
        let total = 0;
        Object.keys(cart).forEach(id => {
            total += cart[id].quantity * cart[id].product.selling_price;
        });
        return total;
    }

    function getTotalAmount() {
        const subtotal = getSubtotalAmount();
        const discount = getDiscountAmount();
        return Math.max(0, subtotal - discount);
    }

    function handleDiscountChange() {
        const subtotal = getSubtotalAmount();
        const discount = getDiscountAmount();
        const total = Math.max(0, subtotal - discount);
        document.getElementById('cart-total').innerText = formatRp(total);
        calculateChange();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty-state');
        const badgeCount = document.getElementById('cart-badge-count');
        const keys = Object.keys(cart);

        let totalSubtotal = 0;
        let totalItemsCount = 0;

        if (keys.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            document.getElementById('cart-subtotal').innerText = 'Rp 0';
            document.getElementById('cart-total').innerText = 'Rp 0';
            document.getElementById('cart-count').innerText = '0';
            if (badgeCount) badgeCount.innerText = '0 item';
            document.getElementById('btn-checkout').disabled = true;
            calculateChange();
            return;
        }

        emptyState.classList.add('hidden');
        container.innerHTML = '';

        keys.forEach(id => {
            const it = cart[id];
            const lineTotal = it.quantity * it.product.selling_price;
            totalSubtotal += lineTotal;
            totalItemsCount += it.quantity;

            const row = document.createElement('div');
            row.className = 'p-3 rounded-xl bg-[#faf7f0] border border-[#e8e2d4] space-y-2 transition hover:border-[#c59b27] shadow-2xs';
            row.innerHTML = `
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex-1 min-w-0">
                        <h5 class="text-xs font-bold text-[#262017] leading-snug line-clamp-2">${it.product.name}</h5>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[9px] text-[#8a681c] font-mono font-bold bg-[#f3ecdc] px-1.5 py-0.5 rounded border border-[#dfb15b]/30">${it.product.sku}</span>
                            <span class="text-[11px] text-[#736858] font-medium">${formatRp(it.product.selling_price)} / ${it.product.unit}</span>
                        </div>
                    </div>
                    <button type="button" onclick="removeFromCart(${id})" title="Hapus dari keranjang" class="w-6 h-6 rounded-lg text-rose-500 hover:text-white hover:bg-rose-600 flex items-center justify-center transition border border-rose-200 bg-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between pt-1.5 border-t border-[#eee7d8]">
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="updateCartQty(${id}, ${it.quantity - 1})"
                            class="w-6 h-6 rounded-lg bg-white border border-[#dcd4c5] text-[#362f25] flex items-center justify-center font-bold text-xs hover:bg-[#c59b27] hover:text-stone-950 transition">-</button>
                        <input type="number" min="1" max="${it.product.stock}" value="${it.quantity}"
                            onchange="updateCartQty(${id}, this.value)"
                            class="w-12 text-center bg-white border border-[#dcd4c5] rounded-lg py-0.5 text-xs font-bold text-[#262017] focus:ring-1 focus:ring-[#c59b27]">
                        <button type="button" onclick="updateCartQty(${id}, ${it.quantity + 1})"
                            class="w-6 h-6 rounded-lg bg-white border border-[#dcd4c5] text-[#362f25] flex items-center justify-center font-bold text-xs hover:bg-[#c59b27] hover:text-stone-950 transition">+</button>
                        <span class="text-[9px] text-[#8a7f70] ml-1">Maks: ${it.product.stock}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] text-[#8a7f70] block">Subtotal</span>
                        <span class="font-serif-classic font-bold text-xs text-[#262017]">${formatRp(lineTotal)}</span>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        const discount = getDiscountAmount();
        const grandTotal = Math.max(0, totalSubtotal - discount);

        document.getElementById('cart-subtotal').innerText = formatRp(totalSubtotal);
        document.getElementById('cart-total').innerText = formatRp(grandTotal);
        document.getElementById('cart-count').innerText = totalItemsCount;
        if (badgeCount) badgeCount.innerText = totalItemsCount + ' item';

        document.getElementById('btn-checkout').disabled = false;
        calculateChange();
    }

    function handlePaymentMethodChange() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const cashSection = document.getElementById('cash-input-section');

        if (method === 'cash') {
            cashSection.classList.remove('hidden');
        } else {
            cashSection.classList.add('hidden');
        }
        calculateChange();
    }

    function setExactAmount() {
        const total = getTotalAmount();
        document.getElementById('cash-tendered').value = total;
        calculateChange();
    }

    function addPresetCash(val) {
        const cur = parseFloat(document.getElementById('cash-tendered').value) || 0;
        document.getElementById('cash-tendered').value = cur + val;
        calculateChange();
    }

    function calculateChange() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const total = getTotalAmount();
        const btn = document.getElementById('btn-checkout');
        const errEl = document.getElementById('cash-error');

        if (total === 0 || Object.keys(cart).length === 0) {
            btn.disabled = true;
            document.getElementById('cash-change').innerText = 'Rp 0';
            errEl.classList.add('hidden');
            return;
        }

        if (method !== 'cash') {
            btn.disabled = false;
            errEl.classList.add('hidden');
            return;
        }

        const tenderedInput = document.getElementById('cash-tendered');
        const tendered = parseFloat(tenderedInput.value) || 0;
        const change = tendered - total;

        if (tendered < total) {
            btn.disabled = true;
            document.getElementById('cash-change').innerText = 'Rp 0';
            errEl.innerText = 'Uang pembayaran kurang ' + formatRp(total - tendered);
            errEl.classList.remove('hidden');
        } else {
            btn.disabled = false;
            document.getElementById('cash-change').innerText = formatRp(change);
            errEl.classList.add('hidden');
        }
    }

    // Checkout with atomic locking and anti double-submit protection
    let isSubmitting = false;

    async function processCheckout() {
        if (isSubmitting) return;

        const keys = Object.keys(cart);
        if (keys.length === 0) {
            alert('Keranjang belanja masih kosong!');
            return;
        }

        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const total = getTotalAmount();
        const tendered = parseFloat(document.getElementById('cash-tendered').value) || 0;

        if (method === 'cash' && tendered < total) {
            alert('Uang pembayaran tunai kurang dari total belanja.');
            return;
        }

        // Lock button to prevent double-submit
        isSubmitting = true;
        const btn = document.getElementById('btn-checkout');
        const spinner = document.getElementById('checkout-spinner');
        const btnText = document.getElementById('checkout-btn-text');

        btn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.innerText = 'Memproses Transaksi...';

        const payload = {
            items: keys.map(id => ({
                id: parseInt(id),
                quantity: cart[id].quantity
            })),
            payment_method: method,
            discount_amount: getDiscountAmount(),
            amount_tendered: method === 'cash' ? tendered : total,
            notes: document.getElementById('cart-notes')?.value || null
        };

        try {
            const response = await fetch("{{ route('pos.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Redirect immediately to receipt view
                window.location.href = data.redirect_url;
            } else {
                alert('Gagal memproses transaksi: ' + (data.message || 'Terjadi kesalahan sistem.'));
                isSubmitting = false;
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Selesaikan Pembayaran & Cetak Struk';
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan atau server: ' + err.message);
            isSubmitting = false;
            btn.disabled = false;
            spinner.classList.add('hidden');
            btnText.innerText = 'Selesaikan Pembayaran & Cetak Struk';
        }
    }

    // POS Keyboard Shortcut Listeners
    window.addEventListener('keydown', function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName : '';
        const isInputActive = activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT';

        // F1: Focus Search Input
        if (e.key === 'F1') {
            e.preventDefault();
            const searchEl = document.getElementById('pos-search-input');
            if (searchEl) {
                searchEl.focus();
                searchEl.select();
            }
            return;
        }

        // "/" (Slash): Quick search when not typing in an input
        if (e.key === '/' && !isInputActive) {
            e.preventDefault();
            const searchEl = document.getElementById('pos-search-input');
            if (searchEl) {
                searchEl.focus();
                searchEl.select();
            }
            return;
        }

        // F2: Focus Cash Tendered Input (Switches to cash method automatically)
        if (e.key === 'F2') {
            e.preventDefault();
            const cashRadio = document.querySelector('input[name="payment_method"][value="cash"]');
            if (cashRadio && !cashRadio.checked) {
                cashRadio.checked = true;
                handlePaymentMethodChange();
            }
            const cashEl = document.getElementById('cash-tendered');
            if (cashEl) {
                cashEl.focus();
                cashEl.select();
            }
            return;
        }

        // F4: Exact Amount (Uang Pas)
        if (e.key === 'F4') {
            e.preventDefault();
            const cashRadio = document.querySelector('input[name="payment_method"][value="cash"]');
            if (cashRadio && !cashRadio.checked) {
                cashRadio.checked = true;
                handlePaymentMethodChange();
            }
            setExactAmount();
            return;
        }

        // F7: Focus Discount Input
        if (e.key === 'F7') {
            e.preventDefault();
            const discEl = document.getElementById('cart-discount');
            if (discEl) {
                discEl.focus();
                discEl.select();
            }
            return;
        }

        // Enter or F8: Selesaikan Pembayaran (Checkout)
        if (e.key === 'Enter' || e.key === 'F8' || (e.ctrlKey && e.key === 'Enter')) {
            const modal = document.getElementById('global-shortcut-modal');
            if (modal && !modal.classList.contains('hidden')) return;

            // If user is currently typing inside search input and has text, let search input handle barcode/item add
            const searchEl = document.getElementById('pos-search-input');
            if (e.key === 'Enter' && searchEl && searchEl === document.activeElement && searchEl.value.trim() !== '') {
                return;
            }

            e.preventDefault();
            const btn = document.getElementById('btn-checkout');
            if (btn && !btn.disabled) {
                processCheckout();
            } else if (Object.keys(cart).length === 0) {
                alert('Keranjang belanja masih kosong! Tambahkan produk terlebih dahulu.');
                searchEl?.focus();
            } else {
                const method = document.querySelector('input[name="payment_method"]:checked')?.value;
                const total = getTotalAmount();
                const tendered = parseFloat(document.getElementById('cash-tendered')?.value) || 0;
                if (method === 'cash' && tendered < total) {
                    alert('Uang pembayaran tunai kurang dari total belanja.');
                    document.getElementById('cash-tendered')?.focus();
                }
            }
            return;
        }

        // Alt + 1: Cash payment method
        if (e.altKey && (e.key === '1' || e.code === 'Digit1')) {
            e.preventDefault();
            const radio = document.querySelector('input[name="payment_method"][value="cash"]');
            if (radio) {
                radio.checked = true;
                handlePaymentMethodChange();
            }
            return;
        }

        // Alt + 2: Transfer payment method
        if (e.altKey && (e.key === '2' || e.code === 'Digit2')) {
            e.preventDefault();
            const radio = document.querySelector('input[name="payment_method"][value="transfer"]');
            if (radio) {
                radio.checked = true;
                handlePaymentMethodChange();
            }
            return;
        }

        // Alt + 3: QRIS payment method
        if (e.altKey && (e.key === '3' || e.code === 'Digit3')) {
            e.preventDefault();
            const radio = document.querySelector('input[name="payment_method"][value="qris"]');
            if (radio) {
                radio.checked = true;
                handlePaymentMethodChange();
            }
            return;
        }

        // Alt + C: Clear Cart
        if (e.altKey && (e.key === 'c' || e.key === 'C')) {
            e.preventDefault();
            clearCart();
            return;
        }

        // Escape: If search has text, clear it; if in input, blur it
        if (e.key === 'Escape') {
            const searchEl = document.getElementById('pos-search-input');
            if (searchEl && searchEl === document.activeElement) {
                if (searchEl.value.trim() !== '') {
                    searchEl.value = '';
                    filterProducts();
                } else {
                    searchEl.blur();
                }
                return;
            }
            if (isInputActive) {
                document.activeElement.blur();
                return;
            }
        }
    });

    // Enter key in cash-tendered input proceeds to checkout if valid
    document.getElementById('cash-tendered')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const btn = document.getElementById('btn-checkout');
            if (btn && !btn.disabled) {
                processCheckout();
            }
        }
    });
</script>
@endsection
