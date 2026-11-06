@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-stone-900 via-amber-950 to-stone-900 text-white p-7 sm:p-9 shadow-xl border border-amber-500/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Pengelolaan Tagihan Meja Resto
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Fitur Open Bill Meja</h2>
                <p class="text-stone-300 text-sm mt-1 max-w-xl">
                    Gabungkan seluruh pesanan pelanggan pada sesi meja yang sama ke dalam satu tagihan kolektif. Kasir dapat mengonfirmasi pelunasan sekaligus saat pelanggan hendak checkout.
                </p>
            </div>
            <div>
                <button type="button" onclick="openNewBillModal()"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-stone-950 text-xs font-extrabold px-5 py-3.5 rounded-2xl shadow-lg transition transform hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Buka Open Bill Baru
                </button>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
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
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Permintaan Open Bill Menunggu Persetujuan Kasir -->
    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-400 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                    <h3 class="font-extrabold text-base text-amber-950 font-serif-classic">
                        Permintaan Buka Open Bill Menunggu Persetujuan ({{ $pendingRequests->count() }} Meja)
                    </h3>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-200/60 px-3 py-1 rounded-full">Permintaan Pelanggan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pendingRequests as $req)
                    <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-2xs flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-amber-900 bg-amber-100 px-2.5 py-1 rounded-lg">{{ $req->table_number }}</span>
                                <span class="text-[10px] text-slate-400">{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-bold text-base text-slate-900 mt-2">{{ $req->table->name ?? 'Meja ' . $req->table_number }}</h4>
                            @if($req->customer_name)
                                <p class="text-xs text-slate-600 mt-1">Pemohon: <strong class="text-slate-800">{{ $req->customer_name }}</strong></p>
                            @endif
                            @if($req->notes)
                                <p class="text-[11px] text-slate-500 italic mt-1.5">"{{ $req->notes }}"</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                            <form action="{{ route('cashier.open_bills.approve', $req->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Setujui Open Bill
                                </button>
                            </form>
                            <form action="{{ route('cashier.open_bills.reject', $req->id) }}" method="POST"
                                data-confirm="Apakah Anda yakin ingin menolak permintaan Open Bill untuk Meja {{ $req->restaurantTable?->table_number ?? '-' }}?"
                                data-confirm-title="Tolak Open Bill"
                                data-confirm-btn="Ya, Tolak Permintaan"
                                data-confirm-type="danger">
                                @csrf
                                <button type="submit" class="py-2.5 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Catatan Kebijakan Open Bill -->
    <div class="bg-amber-50/80 border border-amber-200 text-amber-900 px-5 py-4 rounded-2xl text-xs flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="font-bold">Ketentuan Fitur Open Bill:</strong>
            <p class="mt-0.5 text-amber-800">
                Fitur ini murni digunakan untuk <strong>pengelolaan tagihan</strong>. Kasir tidak diperkenankan menambah, menghapus, atau mengubah item menu. Seluruh menu tetap dipesan mandiri oleh pelanggan lewat scan QR Code meja.
            </p>
        </div>
    </div>

    <!-- Grid Status Meja & Open Bill Aktif -->
    <div>
        <h3 class="font-extrabold text-slate-800 text-lg mb-4">Status Meja Restoran</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($tables as $table)
                @php
                    $activeBill = $table->activeOpenBill;
                @endphp
                <div class="bg-white rounded-3xl border {{ $activeBill ? 'border-amber-400/80 shadow-md ring-2 ring-amber-400/20' : 'border-slate-200/80 shadow-xs' }} p-5 flex flex-col justify-between transition hover:shadow-md">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-base font-black text-slate-900">{{ $table->table_number }}</span>
                            @if($activeBill)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Open Bill Aktif
                                </span>
                            @elseif($table->requestedOpenBill)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-400 animate-pulse">
                                    Permintaan Masuk
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Standar (Per Pesanan)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 font-medium">{{ $table->name }}</p>

                        @if($activeBill)
                            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Nomor Bill:</span>
                                    <span class="font-mono font-bold text-slate-700">{{ $activeBill->bill_number }}</span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Total Pesanan:</span>
                                    <span class="font-bold text-slate-700">{{ $activeBill->sales->count() }} transaksi</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-slate-600 font-bold uppercase text-[11px]">Total Tagihan:</span>
                                    <span class="font-black text-amber-900 text-base">
                                        Rp {{ number_format($activeBill->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @elseif($table->requestedOpenBill)
                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200 text-center space-y-1">
                                <span class="font-bold block">Permintaan Buka Open Bill</span>
                                <span class="text-[11px] text-slate-500 block">Pemohon: {{ $table->requestedOpenBill->customer_name ?: 'Pelanggan' }}</span>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400 py-3 text-center">
                                Belum ada Open Bill aktif di meja ini.
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
                        @if($activeBill)
                            <a href="{{ route('cashier.open_bills.show', $activeBill->id) }}"
                                class="w-full text-center py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs">
                                Kelola & Lunasi Tagihan &rarr;
                            </a>
                        @elseif($table->requestedOpenBill)
                            <form action="{{ route('cashier.open_bills.approve', $table->requestedOpenBill->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Setujui Permintaan
                                </button>
                            </form>
                        @else
                            <button type="button" onclick="openNewBillForTable('{{ $table->table_number }}')"
                                class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-amber-50 hover:text-amber-800 hover:border-amber-300 border border-slate-200 text-slate-700 text-xs font-bold transition">
                                + Buka Open Bill
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Riwayat Open Bill yang Selesai Hari Ini -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6">
        <h4 class="font-bold text-slate-900 text-base mb-1">Riwayat Open Bill Lunas Hari Ini</h4>
        <p class="text-xs text-slate-500 mb-4">Daftar sesi tagihan meja yang telah berhasil diselesaikan dan dilunasi oleh Kasir.</p>

        @if($todayClosedBills->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                Belum ada tagihan Open Bill yang ditutup hari ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase font-semibold">
                            <th class="pb-3">No. Bill</th>
                            <th class="pb-3">Meja</th>
                            <th class="pb-3">Waktu Buka / Tutup</th>
                            <th class="pb-3">Kasir Pelunas</th>
                            <th class="pb-3 text-right">Total Tagihan</th>
                            <th class="pb-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($todayClosedBills as $closed)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 font-mono font-bold text-slate-800">{{ $closed->bill_number }}</td>
                                <td class="py-3 font-bold text-slate-700">{{ $closed->table_number }}</td>
                                <td class="py-3 text-slate-500">
                                    {{ $closed->opened_at->format('H:i') }} &rarr; {{ $closed->closed_at?->format('H:i') }} WIB
                                </td>
                                <td class="py-3 font-medium text-slate-600">{{ $closed->closer->name ?? '-' }}</td>
                                <td class="py-3 text-right font-black text-slate-900">
                                    Rp {{ number_format($closed->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 text-[10px]">
                                        Lunas & Ditutup
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- MODAL BUKA OPEN BILL BARU -->
<div id="newBillModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
        <form method="POST" action="{{ route('cashier.open_bills.store') }}">
            @csrf
            <div class="p-6 bg-gradient-to-r from-stone-900 to-amber-950 text-white flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300">Sesi Tagihan Meja</span>
                    <h3 class="text-xl font-black mt-0.5">Buka Open Bill Meja</h3>
                </div>
                <button type="button" onclick="closeNewBillModal()" class="text-stone-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Nomor Meja</label>
                    <select id="selectModalTable" name="table_number" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-amber-500">
                        @foreach($tables as $tbl)
                            @if(!$tbl->hasActiveOpenBill())
                                <option value="{{ $tbl->table_number }}">{{ $tbl->table_number }} - {{ $tbl->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Catatan Khusus Meja (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Misal: Rombongan VIP, Customer Pak Budi..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="bg-amber-50 p-3.5 rounded-2xl text-[11px] text-amber-900 leading-relaxed">
                    Setelah Open Bill dibuka, pelanggan di meja ini dapat memesan berkali-kali via QR Code dan seluruh total tagihannya otomatis dikumpulkan menjadi satu.
                </div>
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeNewBillModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-md transition">
                    Aktifkan Open Bill Meja
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewBillModal() {
        document.getElementById('newBillModal').classList.remove('hidden');
    }

    function openNewBillForTable(tableNumber) {
        document.getElementById('selectModalTable').value = tableNumber;
        document.getElementById('newBillModal').classList.remove('hidden');
    }

    function closeNewBillModal() {
        document.getElementById('newBillModal').classList.add('hidden');
    }
</script>
@endsection
