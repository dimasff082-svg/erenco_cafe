@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-serif-classic text-2xl font-bold text-[#262017] tracking-tight">Transaksi Pengeluaran</h2>
            <p class="text-xs text-[#786e60] mt-0.5">Pencatatan pembelian bahan baku dapur dan pengeluaran operasional resto (listrik, air, telepon, dll).</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-add-expense').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-[#c59b27] hover:bg-[#a98119] text-stone-950 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Catat Pengeluaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">&times;</button>
        </div>
    @endif

    <!-- 3 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Pengeluaran -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Total Seluruh Pengeluaran</span>
                <span class="text-2xl font-black text-rose-700 mt-1 block">
                    Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-[#8f8573] mt-0.5">{{ $summary['total_count'] }} transaksi pengeluaran tercatat</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Pembelian Bahan Baku -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Pembelian Bahan Baku</span>
                <span class="text-xl font-black text-amber-800 mt-1 block">
                    Rp {{ number_format($summary['raw_material_expense'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-[#8f8573] mt-0.5">Tepung, telur, mentega, gula, & bahan roti</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-[#c59b27] shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Pengeluaran Operasional Lainnya -->
        <div class="bg-white rounded-2xl border border-[#e5dfd3] p-5 shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-[#8f8573] uppercase tracking-wider block">Pengeluaran Lainnya</span>
                <span class="text-xl font-black text-blue-800 mt-1 block">
                    Rp {{ number_format($summary['operational_expense'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-[#8f8573] mt-0.5">Listrik, air, telepon, internet, kemasan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] p-4 shadow-xs">
        <form action="{{ route('warehouse.expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari No. TRX, nama pengeluaran, kategori..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <select name="expense_type" class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                    <option value="all">Semua Jenis Pengeluaran</option>
                    <option value="raw_material" {{ request('expense_type') === 'raw_material' ? 'selected' : '' }}>Pembelian Bahan Baku</option>
                    <option value="operational" {{ request('expense_type') === 'operational' ? 'selected' : '' }}>Pengeluaran Lainnya (Operasional)</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-1.5">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-1/2 bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2 py-2 text-[11px] text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none" title="Dari Tanggal">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-1/2 bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-2 py-2 text-[11px] text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none" title="Sampai Tanggal">
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-[#262017] hover:bg-[#15120c] text-[#f7f5f0] text-xs font-semibold px-4 py-2 rounded-xl transition shadow-xs">
                    Saring
                </button>
                @if(request()->hasAny(['search', 'expense_type', 'date_from', 'date_to']))
                    <a href="{{ route('warehouse.expenses.index') }}" class="bg-[#f0ebd9] hover:bg-[#e4ddc8] text-[#5c5346] text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-[#e5dfd3] shadow-[0_4px_20px_-4px_rgba(40,30,15,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#faf7f0] border-b border-[#e5dfd3] text-[#786e60] uppercase text-[10px] tracking-wider font-semibold">
                        <th class="py-3 px-5">No. TRX & Tanggal</th>
                        <th class="py-3 px-5">Jenis Pengeluaran</th>
                        <th class="py-3 px-5">Nama Pengeluaran / Kategori</th>
                        <th class="py-3 px-5 text-right">Nominal</th>
                        <th class="py-3 px-5 text-center">Metode Bayar</th>
                        <th class="py-3 px-5 text-center">Bukti Nota</th>
                        <th class="py-3 px-5">Dicatat Oleh</th>
                        <th class="py-3 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebd9]">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-[#faf7f0]/60 transition">
                            <td class="py-3 px-5">
                                <span class="font-mono font-bold text-[#262017] block">{{ $exp->expense_number }}</span>
                                <span class="text-[10px] text-[#8f8573]">{{ $exp->expense_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3 px-5">
                                @if($exp->isRawMaterial())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-900 border border-amber-300">
                                        <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        Bahan Baku
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-900 border border-blue-300">
                                        <svg class="w-3 h-3 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Operasional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-5">
                                <span class="font-bold text-[#262017] block text-xs">{{ $exp->title }}</span>
                                <span class="text-[10px] text-[#8f8573]">{{ $exp->category }}</span>
                                @if($exp->notes)
                                    <span class="block text-[10px] text-stone-500 italic mt-0.5">{{ $exp->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right font-black text-sm text-rose-700">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-md font-mono text-[10px] font-bold uppercase {{ $exp->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-purple-50 text-purple-800 border border-purple-200' }}">
                                    {{ $exp->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-center">
                                @if($exp->receipt_proof)
                                    <a href="{{ asset('storage/' . $exp->receipt_proof) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-[#c59b27] hover:text-[#a98119] underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat Foto
                                    </a>
                                @else
                                    <span class="text-[10px] text-stone-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-xs text-[#5c5346]">
                                {{ $exp->user?->name ?? 'Staf' }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <form action="{{ route('warehouse.expenses.destroy', $exp->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pengeluaran {{ $exp->expense_number }} ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs p-1" title="Hapus Pengeluaran">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-[#8f8573]">
                                <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <p class="text-sm font-bold text-[#262017]">Belum Ada Catatan Transaksi Pengeluaran</p>
                                <p class="text-xs text-[#8f8573] mt-0.5">Klik tombol "Catat Pengeluaran Baru" untuk menambahkan pengeluaran bahan baku atau operasional.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-[#e5dfd3] bg-[#faf7f0]">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Catat Pengeluaran Baru -->
<div id="modal-add-expense" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-[#e5dfd3] w-full max-w-lg p-6 space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-[#f0ebd9]">
            <div>
                <h3 class="font-serif-classic font-bold text-base text-[#262017]">Catat Transaksi Pengeluaran</h3>
                <p class="text-[11px] text-[#786e60]">Input pengeluaran pembelian bahan baku atau biaya operasional toko.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('warehouse.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf

            <!-- Pilihan Jenis Pengeluaran -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1.5 font-serif-classic">
                    Jenis Pengeluaran *
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="expense_type" value="raw_material" checked onchange="updateCategorySuggestions('raw_material')" class="peer sr-only">
                        <div class="p-2.5 rounded-xl border border-[#dcd4c5] bg-[#faf7f0] text-center peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-2xs transition">
                            <div class="w-6 h-6 mx-auto mb-1 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold block">Pembelian Bahan Baku</span>
                            <span class="text-[9px] text-[#6b6252] peer-checked:text-stone-900 block">Tepung, telur, gula, mentega</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="expense_type" value="operational" onchange="updateCategorySuggestions('operational')" class="peer sr-only">
                        <div class="p-2.5 rounded-xl border border-[#dcd4c5] bg-[#faf7f0] text-center peer-checked:bg-[#c59b27] peer-checked:text-stone-950 peer-checked:border-[#c59b27] peer-checked:shadow-2xs transition">
                            <div class="w-6 h-6 mx-auto mb-1 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold block">Pengeluaran Lainnya</span>
                            <span class="text-[9px] text-[#6b6252] peer-checked:text-stone-900 block">Listrik, air, telepon, kemasan</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Nama Pengeluaran / Barang -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nama Pengeluaran / Barang *</label>
                <input type="text" name="title" id="expenseTitleInput" required placeholder="Contoh: Pembelian Tepung Terigu Segitiga 25kg"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3.5 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Kategori Pengeluaran -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Kategori / Keterangan</label>
                    <select name="category" id="expenseCategorySelect"
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                        <option value="Bahan Baku Kue & Roti">Bahan Baku Kue & Roti</option>
                        <option value="Bahan Baku Minuman & Kopi">Bahan Baku Minuman & Kopi</option>
                        <option value="Bahan Topping & Isian">Bahan Topping & Isian</option>
                        <option value="Packaging & Kotak Kue">Packaging & Kotak Kue</option>
                        <option value="Listrik PLN">Tagihan Listrik PLN</option>
                        <option value="Air PDAM">Tagihan Air PDAM</option>
                        <option value="Internet & Telepon">Internet & Telepon</option>
                        <option value="Gas Elpiji">Gas Elpiji</option>
                        <option value="Perlengkapan & Kebersihan">Perlengkapan & Kebersihan</option>
                        <option value="Operasional Lainnya">Operasional Lainnya</option>
                    </select>
                </div>

                <!-- Nominal Pengeluaran (Auto format titik ribuan) -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Nominal (Rp) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2 text-xs font-bold text-[#8f8573]">Rp</span>
                        <input type="text" id="expenseAmountDisplay" required placeholder="Contoh: 250.000"
                            oninput="formatRupiahInput(this, 'expenseAmountRaw')"
                            class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl pl-10 pr-3.5 py-2 text-xs text-[#262017] font-bold focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                        <input type="hidden" name="amount" id="expenseAmountRaw" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Tanggal Pengeluaran -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Tanggal Pengeluaran *</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Metode Bayar *</label>
                    <select name="payment_method" required class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-2 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none">
                        <option value="cash" selected>Tunai (Kas Toko)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
            </div>

            <!-- Upload Foto Bukti Nota -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Upload Bukti Nota / Kuitansi (Opsional)</label>
                <input type="file" name="receipt_proof" accept="image/*"
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#5c5346] file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#faf7f0] file:text-[#262017] hover:file:bg-[#ede7da]">
                <p class="text-[10px] text-stone-400 mt-0.5">Format: JPG, PNG, WebP (Maksimal 5MB)</p>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-[#5c5346] mb-1 font-serif-classic">Catatan Keterangan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Catatan toko/supplier, nomor meteran listrik, dll..."
                    class="w-full bg-[#fcfbf9] border border-[#dcd4c5] rounded-xl px-3 py-1.5 text-xs text-[#262017] focus:ring-2 focus:ring-[#c59b27] focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-[#f0ebd9]">
                <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#5c5346] hover:bg-[#f0ebd9]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-[#c59b27] hover:bg-[#a98119] text-stone-950 shadow-xs">
                    Simpan Pengeluaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function formatRupiahInput(input, targetHiddenId) {
        let clean = input.value.replace(/\D/g, '');
        let target = targetHiddenId ? document.getElementById(targetHiddenId) : null;
        if (target) {
            target.value = clean;
        }
        if (clean === '') {
            input.value = '';
            return;
        }
        input.value = new Intl.NumberFormat('id-ID').format(clean);
    }

    function updateCategorySuggestions(type) {
        const titleInput = document.getElementById('expenseTitleInput');
        const catSelect = document.getElementById('expenseCategorySelect');
        if (type === 'raw_material') {
            titleInput.placeholder = "Contoh: Pembelian Tepung Terigu Segitiga 25kg";
            catSelect.value = "Bahan Baku Kue & Roti";
        } else {
            titleInput.placeholder = "Contoh: Pembayaran Tagihan Listrik PLN Bulan Ini";
            catSelect.value = "Listrik PLN";
        }
    }
</script>
@endsection
