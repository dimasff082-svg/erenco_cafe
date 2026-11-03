@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-serif-classic font-bold text-[#262017] tracking-tight">Daftar Menu</h2>
            <p class="text-sm text-[#736858] mt-0.5">Kelola katalog menu makanan & minuman, kategori, foto, dan harga menu.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm shadow-amber-950/20 transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Menu Baru
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama menu atau kode menu..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-3">
                <select name="category_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Menu Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6 text-center w-20">Foto</th>
                        <th class="py-3.5 px-6">Kode Menu</th>
                        <th class="py-3.5 px-6">Nama Menu</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6 text-right">Harga</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-6 text-center">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs mx-auto">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center mx-auto shadow-2xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-mono text-xs font-bold text-slate-800">
                                {{ $product->sku }}
                            </td>
                            <td class="py-4 px-6 text-slate-900 font-bold">
                                <div>{{ $product->name }}</div>
                                @if($product->description)
                                    <p class="text-[11px] text-slate-500 font-normal line-clamp-1 mt-0.5" title="{{ $product->description }}">{{ $product->description }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-600">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $product->category->name ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right text-xs font-bold text-slate-900">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Edit Icon Button -->
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                        title="Ubah Data Menu"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-800 hover:bg-[#c59b27] hover:text-stone-950 border border-amber-300/80 transition shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>

                                    <!-- Toggle Status Icon Button -->
                                    <form action="{{ route('admin.products.toggle', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            title="{{ $product->is_active ? 'Nonaktifkan Menu' : 'Aktifkan Menu' }}"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg {{ $product->is_active ? 'bg-stone-100 text-stone-700 hover:bg-stone-300 border border-stone-300' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-600 hover:text-white border border-emerald-300' }} transition shadow-2xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Delete Icon Button -->
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu \'{{ addslashes($product->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            title="Hapus Menu"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-700 hover:text-white border border-rose-300/80 transition shadow-2xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-sm">
                                Tidak ada data menu yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
