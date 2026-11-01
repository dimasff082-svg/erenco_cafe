@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#201c18] via-[#2d261e] to-[#1a1714] text-white p-6 sm:p-8 shadow-xl border border-[#c59b27]/30">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#c59b27] via-[#a87f1c] to-[#73550b] flex items-center justify-center text-stone-950 shadow-lg border border-[#e5be58]/40 shrink-0 text-2xl">
                    🛍️
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-bold bg-[#c59b27]/20 text-[#eac36c] border border-[#c59b27]/40">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            Operasional Resto
                        </span>
                        <span class="text-[11px] text-[#b3a895] font-semibold">Kasir Langsung (Takeaway)</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight font-serif-classic text-[#f5ebd7]">
                        Transaksi Penjualan
                    </h2>
                    <!-- <p class="text-xs sm:text-sm text-[#c7bfb1] mt-0.5 max-w-xl">
                        Layani pelanggan yang membeli langsung untuk dibungkus/dibawa pulang. Transaksi langsung lunas dan otomatis terkirim ke Layar Dapur.
                    </p> -->
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.transactions.index') : route('cashier.transactions.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2e2922] hover:bg-[#3b342b] text-[#f2ece1] text-xs font-bold border border-[#4a4235] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Riwayat Transaksi Lunas
                </a>
                <button type="button" onclick="resetTakeawayCart()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-stone-200 text-xs font-bold border border-white/10 transition shadow-xs">
                    <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Form
                </button>
            </div>
        </div>
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-[#c59b27]/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Alert Notifikasi -->
    <div id="alertBox" class="hidden rounded-2xl p-4 text-xs font-semibold flex items-center justify-between shadow-xs">
        <span id="alertMessage"></span>
        <button type="button" onclick="closeAlert()" class="opacity-70 hover:opacity-100 text-lg font-bold ml-3">&times;</button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/15 border border-emerald-500/30 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-xs font-semibold">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Split Grid: Left Menu catalog (7 cols), Right Cart & Checkout (5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- LEFT: Katalog Menu Makanan & Minuman -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            <!-- Search & Filter Bar -->
            <div class="bg-white rounded-2xl p-4 border border-[#e5dfd3] shadow-xs space-y-3">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="menuSearch" oninput="filterMenuItems()"
                        placeholder="Cari nama menu atau kode menu (misal: Roti, Kue, Brownies)..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#dcd4c5] bg-[#faf7f0] text-xs font-medium text-[#2c261e] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:border-transparent transition" />
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button type="button" onclick="selectCategory('all')" id="catTab-all"
                        class="cat-tab px-3.5 py-1.5 rounded-xl font-bold transition shrink-0 bg-[#c59b27] text-stone-950 shadow-xs">
                        Semua Menu
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" onclick="selectCategory('cat-{{ $cat->id }}')" id="catTab-cat-{{ $cat->id }}"
                            class="cat-tab px-3.5 py-1.5 rounded-xl font-semibold transition shrink-0 bg-[#faf7f0] text-[#6b6252] hover:bg-[#ede7da] hover:text-[#2c261e] border border-[#e5dfd3]">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @php $allProductsCount = 0; @endphp
                @foreach($categories as $category)
                    @foreach($category->products as $product)
                        @php $allProductsCount++; @endphp
                        <div class="product-card bg-white rounded-2xl border border-[#e5dfd3] p-3 shadow-xs hover:shadow-md hover:border-[#c59b27]/60 transition flex flex-col justify-between group"
                            data-id="{{ $product->id }}"
                            data-name="{{ strtolower($product->name) }}"
                            data-sku="{{ strtolower($product->sku) }}"
                            data-category="cat-{{ $category->id }}"
                            data-price="{{ (float)$product->selling_price }}">

                            <!-- Product Image / Icon -->
                            <div class="relative w-full aspect-square rounded-xl bg-gradient-to-br from-[#faf7f0] to-[#f0e8db] overflow-hidden mb-2.5 flex items-center justify-center border border-[#e8dfcf]">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="text-center p-2">
                                        <span class="text-3xl block">🍰</span>
                                        <span class="text-[10px] font-bold text-[#8f8573] uppercase tracking-wider block mt-1">{{ $category->name }}</span>
                                    </div>
                                @endif
                                <span class="absolute top-2 left-2 bg-[#1f1c18]/80 text-[#eac36c] backdrop-blur-xs text-[9px] font-bold px-2 py-0.5 rounded-md font-mono">
                                    {{ $product->sku }}
                                </span>
                            </div>

                            <!-- Product Details -->
                            <div>
                                <span class="text-[10px] font-semibold text-[#8f8573] uppercase tracking-wider block truncate">
                                    {{ $category->name }}
                                </span>
                                <h3 class="text-xs font-bold text-[#26211a] line-clamp-2 mt-0.5 leading-snug group-hover:text-[#a87f1c] transition">
                                    {{ $product->name }}
                                </h3>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-[#f0e8db] flex items-center justify-between gap-1">
                                <div>
                                    <span class="text-[10px] text-[#8f8573] block leading-none">Harga</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-[#26211a]">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Action Buttons: Add & Qty Counter -->
                                <div id="card-action-{{ $product->id }}" class="shrink-0">
                                    <button type="button" id="btn-add-{{ $product->id }}"
                                        onclick="addToTakeawayCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$product->selling_price }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-xs transition shadow-xs"
                                        title="Tambah ke Pesanan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span>Tambah</span>
                                    </button>
                                    <div id="counter-{{ $product->id }}" class="hidden items-center gap-1 bg-[#faf7f0] border border-[#c59b27] rounded-xl p-0.5 shadow-2xs">
                                        <button type="button" onclick="updateCartItemQty({{ $product->id }}, -1)"
                                            class="w-6 h-6 rounded-lg bg-white text-[#2c261e] border border-[#dcd4c5] flex items-center justify-center font-bold text-xs hover:bg-[#ede7da]">
                                            -
                                        </button>
                                        <span id="qty-{{ $product->id }}" class="font-black text-xs text-stone-950 px-1 min-w-[1.25rem] text-center">
                                            0
                                        </span>
                                        <button type="button" onclick="updateCartItemQty({{ $product->id }}, 1)"
                                            class="w-6 h-6 rounded-lg bg-[#c59b27] text-stone-950 flex items-center justify-center font-bold text-xs hover:bg-[#a87f1c]">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

            <!-- Empty Search State -->
            <div id="noMatchState" class="hidden bg-white rounded-2xl p-10 text-center border border-[#e5dfd3] shadow-xs">
                <span class="text-4xl block mb-2">🔍</span>
                <h4 class="text-sm font-bold text-[#26211a]">Menu tidak ditemukan</h4>
                <p class="text-xs text-[#8f8573] mt-1">Coba gunakan kata kunci pencarian atau kategori lain.</p>
            </div>
        </div>

        <!-- RIGHT: Form Pesanan Bawa Pulang & Kasir Checkout -->
        <div class="lg:col-span-5 xl:col-span-4 sticky top-20 space-y-4">
            <div class="bg-white rounded-3xl border border-[#e5dfd3] shadow-lg overflow-hidden flex flex-col">
                <!-- Cart Header -->
                <div class="p-4 sm:p-5 bg-gradient-to-r from-[#1f1c18] to-[#2d261e] text-[#f5ebd7] flex items-center justify-between border-b border-[#3d3428]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#c59b27] text-stone-950 flex items-center justify-center font-bold text-base shadow-xs">
                            🛍️
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold tracking-tight font-serif-classic">Keranjang Bawa Pulang</h3>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#c59b27]">Kemasan Takeaway</span>
                        </div>
                    </div>
                    <span id="cartCountBadge" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#c59b27] text-stone-950">
                        0 Item
                    </span>
                </div>

                <!-- Customer Details Form -->
                <div class="p-4 bg-[#faf7f0] border-b border-[#e5dfd3] space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#6b6252] mb-1">
                            Nama Pelanggan (Opsional)
                        </label>
                        <input type="text" id="customerNameInput" placeholder="Contoh: Ibu Rina / Pelanggan Bawa Pulang"
                            class="w-full px-3.5 py-2 rounded-xl border border-[#dcd4c5] bg-white text-xs font-semibold text-[#2c261e] focus:outline-none focus:ring-2 focus:ring-[#c59b27]" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#6b6252] mb-1">
                            Catatan Kemasan / Pesanan
                        </label>
                        <input type="text" id="orderNotesInput" placeholder="Contoh: Pisahkan sambal, bungkus 2 kotak terpisah"
                            class="w-full px-3.5 py-2 rounded-xl border border-[#dcd4c5] bg-white text-xs text-[#2c261e] focus:outline-none focus:ring-2 focus:ring-[#c59b27]" />
                    </div>
                </div>

                <!-- Cart Items Container -->
                <div class="p-4 max-h-[340px] overflow-y-auto">
                    <!-- Cart Empty State (Permanent element, hidden when has items) -->
                    <div id="cartEmptyState" class="py-12 text-center text-[#8f8573]">
                        <span class="text-4xl block mb-2">🛍️</span>
                        <p class="text-xs font-bold text-[#26211a]">Keranjang Masih Kosong</p>
                        <p class="text-[11px] text-[#8f8573] mt-0.5">Pilih menu dari katalog di sebelah kiri untuk membuat pesanan.</p>
                    </div>

                    <!-- Dynamic Cart Item List (Injected here) -->
                    <div id="cartItemsList" class="space-y-2.5 divide-y divide-[#f0e8db]"></div>
                </div>

                <!-- Payment Details Section -->
                <div class="p-4 sm:p-5 bg-[#faf7f0] border-t border-[#e5dfd3] space-y-3.5">
                    <!-- Subtotal & Total -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-[#6b6252]">
                            <span>Subtotal Menu</span>
                            <span class="font-bold text-[#2c261e]" id="subtotalDisplay">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-[#e5dfd3]">
                            <span class="font-extrabold text-sm text-[#2c261e]">Total Tagihan</span>
                            <span class="font-black text-xl text-[#a87f1c]" id="totalAmountDisplay">Rp 0</span>
                        </div>
                    </div>

                    <!-- Payment Method Select -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#6b6252] mb-1.5">
                            Metode Pembayaran
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="cash" checked onchange="handlePaymentMethodChange()" class="peer sr-only">
                                <div class="px-2 py-2 rounded-xl border border-[#dcd4c5] bg-white text-center text-xs font-bold text-[#2c261e] peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-xs transition">
                                    💵 Tunai
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="qris" onchange="handlePaymentMethodChange()" class="peer sr-only">
                                <div class="px-2 py-2 rounded-xl border border-[#dcd4c5] bg-white text-center text-xs font-bold text-[#2c261e] peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-xs transition">
                                    📱 QRIS
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="transfer" onchange="handlePaymentMethodChange()" class="peer sr-only">
                                <div class="px-2 py-2 rounded-xl border border-[#dcd4c5] bg-white text-center text-xs font-bold text-[#2c261e] peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-xs transition">
                                    🏦 Transfer
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Cash Calculation Box (Shown only for cash) -->
                    <div id="cashCalculationBox" class="space-y-2 pt-2 border-t border-[#e5dfd3]">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-[#6b6252]">
                                    Uang Diterima (Tunai) <span class="text-rose-600">*</span>
                                </label>
                                <button type="button" onclick="setExactAmount()" class="text-[10px] font-extrabold text-[#a87f1c] hover:underline cursor-pointer">
                                    ⚡ Uang Pas
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-[#8f8573]">
                                    Rp
                                </span>
                                <input type="text" id="amountTenderedInput" inputmode="numeric" oninput="formatRupiahInput(this)" placeholder="0"
                                    class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-[#dcd4c5] bg-white text-sm font-black text-[#2c261e] focus:outline-none focus:ring-2 focus:ring-[#c59b27]" />
                            </div>
                        </div>

                        <!-- Quick Denomination Buttons -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" onclick="setCashTendered(10000)" class="py-1 rounded-lg bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-[10px] font-bold text-[#2c261e]">
                                10.000
                            </button>
                            <button type="button" onclick="setCashTendered(20000)" class="py-1 rounded-lg bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-[10px] font-bold text-[#2c261e]">
                                20.000
                            </button>
                            <button type="button" onclick="setCashTendered(50000)" class="py-1 rounded-lg bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-[10px] font-bold text-[#2c261e]">
                                50.000
                            </button>
                            <button type="button" onclick="setCashTendered(100000)" class="py-1 rounded-lg bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-[10px] font-bold text-[#2c261e]">
                                100.000
                            </button>
                        </div>

                        <!-- Change Amount Display -->
                        <div class="p-2.5 rounded-xl bg-white border border-[#dcd4c5] flex items-center justify-between">
                            <span class="text-xs font-bold text-[#6b6252]">Kembalian:</span>
                            <span class="text-sm font-black text-emerald-700" id="changeAmountDisplay">Rp 0</span>
                        </div>
                    </div>

                    <!-- Process Button -->
                    <button type="button" id="btnProcessCheckout" onclick="processTakeawayCheckout()"
                        class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#c59b27] via-[#a87f1c] to-[#8a681c] hover:from-[#d4a833] hover:to-[#99741f] active:scale-[0.98] text-stone-950 font-black text-sm tracking-wide shadow-lg shadow-amber-950/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Bayar & Kirim ke Dapur</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Takeaway Orders Today -->
    <div class="bg-white rounded-3xl border border-[#e5dfd3] p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0e8db]">
            <div>
                <h3 class="text-base font-extrabold text-[#26211a] font-serif-classic">
                    Riwayat Pesanan Bawa Pulang Hari Ini
                </h3>
                <p class="text-xs text-[#8f8573]">Daftar pesanan takeaway langsung kasir untuk cetak ulang struk atau pantau dapur.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#faf7f0] text-[#6b6252] border border-[#e5dfd3]">
                {{ $todayTakeaways->count() }} Transaksi
            </span>
        </div>

        @if($todayTakeaways->isEmpty())
            <div class="py-8 text-center text-xs text-[#8f8573]">
                Belum ada transaksi bawa pulang (takeaway) yang tercatat hari ini.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
                @foreach($todayTakeaways as $sale)
                    <div class="p-3.5 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] flex flex-col justify-between space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-mono text-xs font-black text-[#a87f1c] block">{{ $sale->transaction_number }}</span>
                                <span class="text-[11px] font-bold text-[#2c261e] block truncate max-w-[140px]">{{ $sale->customer_name }}</span>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md {{ $sale->order_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($sale->order_status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $sale->order_status === 'completed' ? 'Selesai' : ($sale->order_status === 'processing' ? 'Dimasak' : 'Menunggu Dapur') }}
                            </span>
                        </div>

                        <div class="text-[11px] text-[#6b6252] space-y-0.5">
                            <div class="flex justify-between">
                                <span>Total Item</span>
                                <span class="font-bold">{{ $sale->items->sum('quantity') }} porsi</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tagihan</span>
                                <span class="font-extrabold text-[#2c261e]">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] text-[#8f8573]">
                                <span>Waktu</span>
                                <span>{{ $sale->transaction_date->format('H:i') }} WIB</span>
                            </div>
                        </div>

                        <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank"
                            class="w-full text-center py-1.5 rounded-xl bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-xs font-bold text-[#2c261e] transition shadow-2xs flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#8f8573]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Lihat / Cetak Struk
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Sukses Transaksi Takeaway -->
<div id="successModal" class="hidden fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-2xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-[#e5dfd3] animate-in fade-in zoom-in duration-200">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl shadow-sm">
            ✓
        </div>
        <div>
            <h3 class="text-lg font-black text-[#26211a]">Transaksi Takeaway Berhasil!</h3>
            <p class="text-xs text-[#6b6252] mt-1" id="modalSuccessMessage">
                Pesanan telah dibayar lunas dan terkirim otomatis ke Layar Dapur untuk dibungkus.
            </p>
        </div>

        <div class="p-3 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] text-xs space-y-1 font-mono">
            <div class="flex justify-between">
                <span class="text-[#8f8573]">Kembalian:</span>
                <span class="font-bold text-emerald-700" id="modalChangeAmount">Rp 0</span>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <a id="modalReceiptLink" href="#" target="_blank"
                class="w-full py-2.5 px-4 rounded-xl bg-[#c59b27] hover:bg-[#a87f1c] text-stone-950 font-extrabold text-xs transition flex items-center justify-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Struk Pembelian
            </a>
            <a href="{{ auth()->user()->isAdmin() ? route('admin.transactions.index') : route('cashier.transactions.index') }}"
                class="w-full py-2 px-4 rounded-xl bg-[#2e2922] hover:bg-[#3d362c] text-[#f2ece1] text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Lihat di Riwayat Transaksi Lunas
            </a>
            <button type="button" onclick="closeSuccessModalAndReset()"
                class="w-full py-2 px-4 rounded-xl bg-white hover:bg-[#ede7da] border border-[#dcd4c5] text-xs font-bold text-[#2c261e] transition">
                Tutup & Transaksi Baru
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State Keranjang Takeaway
    let takeawayCart = {}; // { [productId]: { id, name, price, quantity, notes } }
    let activeCategoryFilter = 'all';

    function formatRupiah(number) {
        return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
    }

    // Format titik otomatis saat kasir mengetik nominal uang tunai
    function formatRupiahInput(input) {
        let val = input.value.replace(/\D/g, '');
        if (val === '') {
            input.value = '';
        } else {
            input.value = Number(val).toLocaleString('id-ID');
        }
        calculateChange();
    }

    // Ambil nilai angka mentah dari input uang yang diterima
    function getRawAmountTendered() {
        const val = document.getElementById('amountTenderedInput').value;
        return parseInt(val.replace(/\D/g, ''), 10) || 0;
    }

    function selectCategory(catId) {
        activeCategoryFilter = catId;
        document.querySelectorAll('.cat-tab').forEach(tab => {
            tab.classList.remove('bg-[#c59b27]', 'text-stone-950', 'shadow-xs');
            tab.classList.add('bg-[#faf7f0]', 'text-[#6b6252]', 'border', 'border-[#e5dfd3]');
        });
        const activeTab = document.getElementById('catTab-' + catId);
        if (activeTab) {
            activeTab.classList.add('bg-[#c59b27]', 'text-stone-950', 'shadow-xs');
            activeTab.classList.remove('bg-[#faf7f0]', 'text-[#6b6252]', 'border', 'border-[#e5dfd3]');
        }
        filterMenuItems();
    }

    function filterMenuItems() {
        const query = (document.getElementById('menuSearch').value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const sku = card.getAttribute('data-sku');
            const cat = card.getAttribute('data-category');

            const matchCat = (activeCategoryFilter === 'all' || cat === activeCategoryFilter);
            const matchQuery = (!query || name.includes(query) || sku.includes(query));

            if (matchCat && matchQuery) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noMatch = document.getElementById('noMatchState');
        if (visibleCount === 0) {
            noMatch.classList.remove('hidden');
        } else {
            noMatch.classList.add('hidden');
        }
    }

    function addToTakeawayCart(id, name, price) {
        if (!takeawayCart[id]) {
            takeawayCart[id] = {
                id: id,
                name: name,
                price: Number(price),
                quantity: 1,
                notes: ''
            };
        } else {
            takeawayCart[id].quantity += 1;
        }
        updateMenuCardUI(id);
        renderTakeawayCart();
    }

    function updateCartItemQty(id, delta) {
        if (!takeawayCart[id]) return;
        takeawayCart[id].quantity += delta;
        if (takeawayCart[id].quantity <= 0) {
            delete takeawayCart[id];
        }
        updateMenuCardUI(id);
        renderTakeawayCart();
    }

    function removeCartItem(id) {
        delete takeawayCart[id];
        updateMenuCardUI(id);
        renderTakeawayCart();
    }

    function updateCartItemNotes(id, notes) {
        if (takeawayCart[id]) {
            takeawayCart[id].notes = notes;
        }
    }

    // Sinkronkan tombol tambah & counter jumlah di kartu menu
    function updateMenuCardUI(id) {
        const btnAdd = document.getElementById(`btn-add-${id}`);
        const counter = document.getElementById(`counter-${id}`);
        const qtyText = document.getElementById(`qty-${id}`);

        if (takeawayCart[id] && takeawayCart[id].quantity > 0) {
            if (btnAdd) btnAdd.classList.add('hidden');
            if (counter) {
                counter.classList.remove('hidden');
                counter.classList.add('inline-flex');
            }
            if (qtyText) qtyText.innerText = takeawayCart[id].quantity;
        } else {
            if (btnAdd) btnAdd.classList.remove('hidden');
            if (counter) {
                counter.classList.add('hidden');
                counter.classList.remove('inline-flex');
            }
            if (qtyText) qtyText.innerText = '0';
        }
    }

    function updateAllMenuCardUIs() {
        document.querySelectorAll('.product-card').forEach(card => {
            const id = card.getAttribute('data-id');
            updateMenuCardUI(id);
        });
    }

    function getCartTotal() {
        return Object.values(takeawayCart).reduce((acc, item) => acc + (item.price * item.quantity), 0);
    }

    function renderTakeawayCart() {
        const container = document.getElementById('cartItemsList');
        const emptyState = document.getElementById('cartEmptyState');
        const countBadge = document.getElementById('cartCountBadge');
        const subtotalDisp = document.getElementById('subtotalDisplay');
        const totalDisp = document.getElementById('totalAmountDisplay');

        const items = Object.values(takeawayCart);
        const totalItemsCount = items.reduce((acc, it) => acc + it.quantity, 0);
        const totalAmount = getCartTotal();

        countBadge.innerText = totalItemsCount + ' Item';
        subtotalDisp.innerText = formatRupiah(totalAmount);
        totalDisp.innerText = formatRupiah(totalAmount);

        if (items.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            calculateChange();
            return;
        }

        emptyState.classList.add('hidden');
        let html = '';

        items.forEach(item => {
            const lineTotal = item.price * item.quantity;
            html += `
                <div class="pt-2.5 pb-1 space-y-1.5 first:pt-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-[#26211a] leading-tight">${item.name}</h4>
                            <span class="text-[11px] text-[#8f8573]">${formatRupiah(item.price)} &times; ${item.quantity}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-black text-[#26211a] block">${formatRupiah(lineTotal)}</span>
                            <button type="button" onclick="removeCartItem(${item.id})" class="text-[10px] text-rose-500 hover:text-rose-700 font-semibold mt-0.5">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1">
                        <div class="flex items-center gap-1.5 bg-white border border-[#dcd4c5] rounded-lg p-0.5 shadow-2xs">
                            <button type="button" onclick="updateCartItemQty(${item.id}, -1)" class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold bg-[#faf7f0] hover:bg-[#ede7da] text-[#2c261e]">
                                -
                            </button>
                            <span class="w-6 text-center text-xs font-black text-[#26211a]">${item.quantity}</span>
                            <button type="button" onclick="updateCartItemQty(${item.id}, 1)" class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold bg-[#faf7f0] hover:bg-[#ede7da] text-[#2c261e]">
                                +
                            </button>
                        </div>

                        <input type="text" value="${item.notes || ''}" onchange="updateCartItemNotes(${item.id}, this.value)"
                            placeholder="Catatan porsi (misal: pedas)"
                            class="flex-1 px-2.5 py-1 rounded-lg border border-[#e5dfd3] bg-white text-[10px] text-[#2c261e] focus:outline-none focus:ring-1 focus:ring-[#c59b27]" />
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        calculateChange();
    }

    function handlePaymentMethodChange() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const cashBox = document.getElementById('cashCalculationBox');
        if (method === 'cash') {
            cashBox.classList.remove('hidden');
        } else {
            cashBox.classList.add('hidden');
        }
        calculateChange();
    }

    function setExactAmount() {
        const total = getCartTotal();
        document.getElementById('amountTenderedInput').value = total > 0 ? Number(total).toLocaleString('id-ID') : '';
        calculateChange();
    }

    function setCashTendered(amount) {
        document.getElementById('amountTenderedInput').value = Number(amount).toLocaleString('id-ID');
        calculateChange();
    }

    function calculateChange() {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        const total = getCartTotal();
        const tendered = getRawAmountTendered();
        const changeDisp = document.getElementById('changeAmountDisplay');

        if (method !== 'cash') {
            changeDisp.innerText = 'Rp 0';
            return;
        }

        if (tendered >= total && total > 0) {
            changeDisp.innerText = formatRupiah(tendered - total);
            changeDisp.className = 'text-sm font-black text-emerald-700';
        } else if (tendered > 0 && tendered < total) {
            changeDisp.innerText = 'Kurang ' + formatRupiah(total - tendered);
            changeDisp.className = 'text-sm font-bold text-rose-600';
        } else {
            changeDisp.innerText = 'Rp 0';
            changeDisp.className = 'text-sm font-black text-stone-500';
        }
    }

    function showAlert(message, type = 'error') {
        const box = document.getElementById('alertBox');
        const msg = document.getElementById('alertMessage');
        msg.innerText = message;
        box.classList.remove('hidden', 'bg-rose-50', 'text-rose-800', 'border-rose-300', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-300');

        if (type === 'error') {
            box.classList.add('bg-rose-50', 'text-rose-800', 'border', 'border-rose-300');
        } else {
            box.classList.add('bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-300');
        }
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function closeAlert() {
        document.getElementById('alertBox').classList.add('hidden');
    }

    function resetTakeawayCart() {
        takeawayCart = {};
        document.getElementById('customerNameInput').value = '';
        document.getElementById('orderNotesInput').value = '';
        document.getElementById('amountTenderedInput').value = '';
        document.querySelector('input[name="payment_method"][value="cash"]').checked = true;
        handlePaymentMethodChange();
        updateAllMenuCardUIs();
        renderTakeawayCart();
        closeAlert();
    }

    async function processTakeawayCheckout() {
        closeAlert();
        const items = Object.values(takeawayCart);
        if (items.length === 0) {
            showAlert('Keranjang pesanan masih kosong. Pilih minimal satu menu untuk takeaway.', 'error');
            return;
        }

        const totalAmount = getCartTotal();
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const rawTendered = getRawAmountTendered();
        const tenderedVal = document.getElementById('amountTenderedInput').value.trim();

        // Validasi wajib untuk Tunai: kolom tidak boleh kosong dan harus >= total tagihan
        if (method === 'cash') {
            if (!tenderedVal || rawTendered <= 0) {
                showAlert('Uang tunai yang diterima belum diisi. Masukkan nominal uang yang diterima atau klik tombol Uang Pas.', 'error');
                document.getElementById('amountTenderedInput').focus();
                return;
            }

            if (rawTendered < totalAmount) {
                showAlert('Uang tunai yang diterima kurang dari total tagihan (' + formatRupiah(totalAmount) + ').', 'error');
                document.getElementById('amountTenderedInput').focus();
                return;
            }
        }

        const customerName = (document.getElementById('customerNameInput').value || '').trim();
        const notes = (document.getElementById('orderNotesInput').value || '').trim();

        const btn = document.getElementById('btnProcessCheckout');
        btn.disabled = true;
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Memproses Transaksi...</span>
        `;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch("{{ route('takeaway.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    customer_name: customerName,
                    notes: notes,
                    payment_method: method,
                    amount_tendered: method === 'cash' ? rawTendered : totalAmount,
                    items: items.map(it => ({
                        id: it.id,
                        quantity: it.quantity,
                        notes: it.notes
                    }))
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Terjadi kesalahan saat memproses transaksi bawa pulang.');
            }

            // Transaksi Sukses! Buka Modal
            document.getElementById('modalSuccessMessage').innerText = data.message;
            document.getElementById('modalChangeAmount').innerText = formatRupiah(data.change_amount || 0);
            document.getElementById('modalReceiptLink').href = data.receipt_url;
            document.getElementById('successModal').classList.remove('hidden');

        } catch (err) {
            showAlert(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    function closeSuccessModalAndReset() {
        document.getElementById('successModal').classList.add('hidden');
        resetTakeawayCart();
        window.location.href = "{{ route('takeaway.index') }}";
    }
</script>
@endpush
