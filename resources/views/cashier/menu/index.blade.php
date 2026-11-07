@extends('layouts.app')

@section('title', 'Katalog & Data Menu')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Page & Consultation Notice -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-[#211d17] via-[#2c261e] to-[#1a1712] p-6 rounded-3xl text-white shadow-xl border border-[#3d3529]">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#dfb15b]/20 text-[#eac36c] border border-[#dfb15b]/30">
                    <svg class="w-3.5 h-3.5 text-[#eac36c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Katalog Kasir • Read Only
                </span>
                <span class="text-xs text-stone-400">Total: {{ $products->total() }} Menu Terdaftar</span>
            </div>
            <h1 class="text-2xl font-serif-classic font-bold tracking-tight text-[#f7f2e8]">Data Menu & Panduan Informasi Rasa</h1>
            <p class="text-xs text-[#b8ab97] max-w-2xl leading-relaxed">
                Halaman ini khusus disediakan bagi Kasir untuk melihat daftar menu lengkap beserta deskripsi rasa, komposisi, dan penyajian guna menjelaskan kepada pelanggan yang bertanya di kasir.
            </p>
        </div>

        <div class="shrink-0 flex items-center gap-3">
            <a href="{{ route('takeaway.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#c59b27] hover:bg-[#b0881e] text-stone-950 font-bold text-xs transition shadow-md hover:scale-105 active:scale-95">
                <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Transaksi Takeaway
            </a>
            <a href="{{ route('cashier.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/15 text-stone-200 font-semibold text-xs transition border border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Pesanan Masuk
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white rounded-3xl p-5 border border-[#e5dfd3] shadow-xs space-y-4">
        <form method="GET" action="{{ route('cashier.menu.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search Input -->
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama menu, kode SKU, atau kata kunci deskripsi..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] text-xs text-[#26211a] placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:bg-white transition">
            </div>

            <!-- Category Selector -->
            <div class="md:col-span-4">
                <select name="category_id" onchange="this.form.submit()"
                    class="w-full px-3.5 py-2.5 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] text-xs text-[#26211a] focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:bg-white transition cursor-pointer">
                    <option value="">Semua Kategori Menu</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Selector -->
            <div class="md:col-span-2">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3.5 py-2.5 rounded-2xl bg-[#faf7f0] border border-[#e5dfd3] text-xs text-[#26211a] focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:bg-white transition cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif (Tersedia)</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Submit & Reset Action -->
            <div class="md:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2.5 rounded-2xl bg-[#c59b27] hover:bg-[#b0881e] text-stone-950 font-bold text-xs flex items-center justify-center transition shadow-2xs">
                    Cari
                </button>
                @if(request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('cashier.menu.index') }}" title="Reset Filter" class="p-2.5 rounded-2xl bg-[#faf7f0] hover:bg-stone-200 text-stone-600 transition border border-[#e5dfd3] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>

        <!-- Quick Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pt-2 pb-1 text-xs border-t border-[#f4efe4]">
            <span class="text-[11px] font-bold text-stone-400 uppercase tracking-wider shrink-0 mr-1">Kategori Cepat:</span>
            <a href="{{ route('cashier.menu.index') }}"
                class="px-3 py-1.5 rounded-xl text-xs font-medium transition shrink-0 {{ !request('category_id') ? 'bg-[#26211a] text-white shadow-xs' : 'bg-[#faf7f0] text-stone-600 hover:bg-[#eee8db] border border-[#e5dfd3]' }}">
                Semua ({{ \App\Models\Product::count() }})
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('cashier.menu.index', array_merge(request()->except('category_id', 'page'), ['category_id' => $cat->id])) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-medium transition shrink-0 {{ request('category_id') == $cat->id ? 'bg-[#c59b27] text-stone-950 font-bold shadow-xs' : 'bg-[#faf7f0] text-stone-600 hover:bg-[#eee8db] border border-[#e5dfd3]' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Menu Grid -->
    @if($products->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-[#e5dfd3] shadow-xs space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-[#faf7f0] border border-[#e5dfd3] flex items-center justify-center mx-auto text-stone-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="font-serif-classic font-bold text-lg text-[#26211a]">Menu Tidak Ditemukan</h3>
            <p class="text-xs text-stone-500 max-w-md mx-auto">
                Tidak ada menu yang sesuai dengan kata kunci pencarian atau filter kategori yang dipilih.
            </p>
            <div class="pt-2">
                <a href="{{ route('cashier.menu.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#faf7f0] hover:bg-[#ece5d8] text-stone-800 text-xs font-semibold border border-[#d8d0c2] transition">
                    Bersihkan Pencarian
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($products as $product)
                <div class="bg-white rounded-3xl border border-[#e5dfd3] shadow-xs hover:shadow-md hover:border-[#dfb15b]/60 transition duration-200 flex flex-col overflow-hidden group">
                    <!-- Photo Header & Badges -->
                    <div class="relative h-44 bg-[#faf7f0] overflow-hidden border-b border-[#f0ebdf]">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-stone-300 bg-gradient-to-b from-[#faf7f0] to-[#eee8db]">
                                <svg class="w-10 h-10 mb-1 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] font-semibold text-stone-400 uppercase tracking-wider">{{ $storeName }} Menu</span>
                            </div>
                        @endif

                        <!-- Category Pill -->
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#26211a]/85 backdrop-blur-xs text-[#f5eee1] border border-stone-700/50 shadow-xs">
                                {{ $product->category?->name ?? 'Tanpa Kategori' }}
                            </span>
                        </div>

                        <!-- Status Pill -->
                        <div class="absolute top-3 right-3">
                            @if($product->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600/90 text-white backdrop-blur-xs shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    Tersedia
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-700/90 text-stone-300 backdrop-blur-xs shadow-xs">
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        <!-- SKU Badge -->
                        <div class="absolute bottom-2.5 left-3">
                            <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded-lg bg-white/90 text-stone-700 backdrop-blur-xs border border-stone-200 shadow-2xs">
                                {{ $product->sku }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-1.5">
                            <h3 class="font-serif-classic font-bold text-base text-[#26211a] leading-snug line-clamp-1 group-hover:text-[#916b14] transition">
                                {{ $product->name }}
                            </h3>

                            <!-- Price -->
                            <div class="flex items-baseline gap-1 text-[#8c6716]">
                                <span class="text-xs font-semibold">Rp</span>
                                <span class="font-serif-classic font-extrabold text-lg text-[#26211a]">
                                    {{ number_format($product->selling_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-stone-500 font-medium">/ {{ $product->unit }}</span>
                            </div>

                            <!-- Description Preview -->
                            <div class="pt-1">
                                @if($product->description)
                                    <p class="text-xs text-[#6e6354] line-clamp-2 leading-relaxed">
                                        {{ $product->description }}
                                    </p>
                                @else
                                    <p class="text-xs text-stone-400 italic">
                                        Belum ada catatan deskripsi menu.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button: Open Detail Consultation Modal -->
                        <div class="pt-2 border-t border-[#f4efe4]">
                            <button type="button"
                                onclick="openMenuDetailModal({{ json_encode([
                                    'name' => $product->name,
                                    'sku' => $product->sku,
                                    'category' => $product->category?->name ?? 'Tanpa Kategori',
                                    'price_formatted' => 'Rp ' . number_format($product->selling_price, 0, ',', '.'),
                                    'unit' => $product->unit,
                                    'is_active' => $product->is_active,
                                    'image_url' => $product->image_url,
                                    'description' => $product->description,
                                ]) }})"
                                class="w-full flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-[#faf7f0] hover:bg-[#c59b27] text-[#4a3e2e] hover:text-stone-950 font-bold text-xs transition border border-[#e5dfd3] hover:border-[#c59b27] shadow-2xs group-hover:bg-[#faf4e6]">
                                <svg class="w-4 h-4 text-[#8a681c] group-hover:text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat Penjelasan Menu
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Modal Detail & Panduan Konsultasi Menu (Read Only) -->
<div id="menu-detail-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-xl bg-white rounded-3xl border border-[#e5dfd3] shadow-2xl overflow-hidden transform transition-all">
        <!-- Modal Top Banner -->
        <div class="relative h-48 bg-[#faf7f0] overflow-hidden border-b border-[#e5dfd3]">
            <img id="modal-menu-image" src="" alt="Foto Menu" class="w-full h-full object-cover hidden">
            <div id="modal-menu-fallback" class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-b from-[#2a241b] to-[#1c1812] text-stone-200">
                <svg class="w-12 h-12 mb-2 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-xs font-serif-classic font-bold text-[#eac36c] tracking-widest uppercase">{{ $storeName }}</span>
            </div>

            <!-- Close Button -->
            <button type="button" onclick="closeMenuDetailModal()"
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-stone-950/60 hover:bg-stone-950 text-white flex items-center justify-center backdrop-blur-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Status Pill on Image -->
            <div class="absolute bottom-3 left-4 flex items-center gap-2">
                <span id="modal-menu-category" class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-white/90 text-stone-800 backdrop-blur-xs shadow-xs"></span>
                <span id="modal-menu-sku" class="font-mono px-2 py-0.5 rounded-lg text-[10px] font-bold bg-stone-900/80 text-white backdrop-blur-xs"></span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-5">
            <!-- Title & Price -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="modal-menu-name" class="font-serif-classic font-extrabold text-xl text-[#26211a]"></h3>
                    <div id="modal-menu-status" class="mt-1"></div>
                </div>
                <div class="text-right shrink-0">
                    <div id="modal-menu-price" class="font-serif-classic font-extrabold text-2xl text-[#8a681c]"></div>
                    <span id="modal-menu-unit" class="text-xs text-stone-500 font-medium"></span>
                </div>
            </div>

            <!-- Full Description Section -->
            <div class="bg-[#faf7f0] p-4 rounded-2xl border border-[#ece4d6] space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-[#8a681c] uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Deskripsi & Karakteristik Menu
                </div>
                <p id="modal-menu-description" class="text-xs text-[#4a3e2e] leading-relaxed whitespace-pre-line"></p>
            </div>

            <!-- Cashier Consultation Tip -->
            <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-950 text-xs">
                <svg class="w-4 h-4 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="space-y-0.5">
                    <span class="font-bold">Tips Penjelasan ke Pelanggan:</span>
                    <p class="text-[11px] text-amber-900 leading-normal">
                        Gunakan informasi deskripsi di atas saat pelanggan menanyakan bahan makanan, rasa pedas/manis, atau rekomendasi menu terbaik.
                    </p>
                </div>
            </div>

            <!-- Modal Action: Tutup -->
            <div class="pt-2 flex justify-end">
                <button type="button" onclick="closeMenuDetailModal()"
                    class="px-5 py-2.5 rounded-2xl bg-[#26211a] hover:bg-[#3d3429] text-white font-bold text-xs transition shadow-xs">
                    Tutup Informasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openMenuDetailModal(data) {
        document.getElementById('modal-menu-name').textContent = data.name;
        document.getElementById('modal-menu-sku').textContent = data.sku;
        document.getElementById('modal-menu-category').textContent = data.category;
        document.getElementById('modal-menu-price').textContent = data.price_formatted;
        document.getElementById('modal-menu-unit').textContent = 'per ' + data.unit;

        // Image Handling
        const imgEl = document.getElementById('modal-menu-image');
        const fallbackEl = document.getElementById('modal-menu-fallback');
        if (data.image_url) {
            imgEl.src = data.image_url;
            imgEl.classList.remove('hidden');
            fallbackEl.classList.add('hidden');
        } else {
            imgEl.classList.add('hidden');
            fallbackEl.classList.remove('hidden');
        }

        // Status
        const statusEl = document.getElementById('modal-menu-status');
        if (data.is_active) {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Status: Menu Tersedia</span>';
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-700 border border-stone-300">Status: Nonaktif</span>';
        }

        // Description
        const descEl = document.getElementById('modal-menu-description');
        if (data.description && data.description.trim() !== '') {
            descEl.textContent = data.description;
            descEl.classList.remove('italic', 'text-stone-400');
            descEl.classList.add('text-[#4a3e2e]');
        } else {
            descEl.textContent = 'Belum ada catatan deskripsi tambahan untuk menu ini.';
            descEl.classList.add('italic', 'text-stone-400');
            descEl.classList.remove('text-[#4a3e2e]');
        }

        const modal = document.getElementById('menu-detail-modal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMenuDetailModal() {
        const modal = document.getElementById('menu-detail-modal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Close on backdrop click
    document.getElementById('menu-detail-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMenuDetailModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenuDetailModal();
        }
    });
</script>
@endsection

