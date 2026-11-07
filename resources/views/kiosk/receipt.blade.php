<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f5ef]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - {{ $sale->transaction_number }} | {{ $storeName }} Kiosk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-receipt, #printable-receipt * {
                visibility: visible;
            }
            #printable-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 80mm;
                margin: 0 auto;
                padding: 5px;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-[#2c261e] bg-[#f7f5ef] overflow-y-auto p-4 sm:p-8">

    <div class="max-w-md mx-auto w-full space-y-6">

        <!-- Top Success Banner (No Print) -->
        <div class="no-print text-center space-y-2">
            @if($sale->isPaid())
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-lg font-black text-[#26211a]">Pesanan Lunas & Dikirim ke Dapur!</h2>
                <p class="text-xs text-[#786e60]">Pembayaran QRIS berhasil. Dapur sedang menyiapkan pesanan Anda.</p>
            @else
                <div class="w-14 h-14 rounded-2xl bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center mx-auto text-2xl shadow-sm">
                    <svg class="w-7 h-7 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h2 class="text-lg font-black text-[#26211a]">Pesanan Berhasil Dibuat!</h2>
                <p class="text-xs text-[#786e60]">Silakan tunjukkan struk ini dan bayar di kasir. Pesanan akan dimasak Dapur setelah pembayaran lunas.</p>
            @endif

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold">
                <svg class="w-3.5 h-3.5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kembali ke menu dalam <span id="countdownTimer" class="font-black text-amber-950">30</span> detik</span>
            </div>
        </div>

        <!-- Action Buttons (No Print) -->
        <div class="no-print grid grid-cols-2 gap-3">
            <button type="button" onclick="window.print()"
                class="py-3 px-4 rounded-2xl bg-[#262017] hover:bg-[#15120c] active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-[#c59b27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Struk</span>
            </button>

            <a href="{{ route('kiosk.index') }}"
                class="py-3 px-4 rounded-2xl bg-[#c59b27] hover:bg-[#a87f1c] active:scale-95 text-stone-950 font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Pesan Lagi / Selesai</span>
            </a>
        </div>

        <!-- Digital Receipt Card -->
        <div id="printable-receipt" class="bg-white rounded-3xl border border-[#e5dfd3] shadow-xl p-7 font-sans">
            <!-- Header Toko -->
            <div class="text-center pb-4 border-b border-dashed border-slate-300">
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-14 h-14 mx-auto object-contain mb-2 drop-shadow-sm" onerror="this.src='{{ asset('images/default-cake.png') }}'">
                <h3 class="font-extrabold text-lg text-slate-900 tracking-tight uppercase leading-tight">
                    <span>{{ $storeNameLine1 }}</span>
                    @if(!empty($storeNameLine2))
                        <span class="block text-sm font-bold text-slate-700 mt-0.5">{{ $storeNameLine2 }}</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-500 mt-1">{{ $storeAddress }}</p>
                <p class="text-xs font-semibold text-slate-700 mt-0.5">Telp / WA: {{ $storePhone }}</p>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                    Layar Pemesanan Mandiri (Kiosk)
                </span>
            </div>

            <!-- Metadata Pesanan -->
            <div class="py-3.5 border-b border-dashed border-slate-300 text-xs space-y-1.5 font-mono">
                <div class="flex justify-between">
                    <span class="text-slate-400">No. Transaksi</span>
                    <span class="font-bold text-slate-900">{{ $sale->transaction_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tipe Pesanan</span>
                    <span class="text-blue-700 font-bold">Layar Mandiri (Kiosk)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama Pelanggan</span>
                    <span class="font-bold text-slate-800">{{ $sale->customer_name }}</span>
                </div>
                @if($sale->customer_phone)
                    <div class="flex justify-between">
                        <span class="text-slate-400">No. HP Pelanggan</span>
                        <span class="font-semibold text-slate-700">{{ $sale->customer_phone }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-400">Waktu Transaksi</span>
                    <span class="text-slate-700">{{ $sale->transaction_date->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kasir Bertugas</span>
                    <span class="text-slate-700 font-semibold">{{ $sale->confirmer?->name ?? ($sale->user?->name ?? 'Kasir Shift') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status Bayar</span>
                    @if($sale->isPaid())
                        <span class="text-emerald-700 font-bold uppercase tracking-wider">Lunas</span>
                    @else
                        <span class="text-amber-700 font-bold uppercase tracking-wider">Menunggu Bayar di Kasir</span>
                    @endif
                </div>
            </div>

            <!-- Daftar Menu Item -->
            <div class="py-3.5 border-b border-dashed border-slate-300">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-slate-400 text-left font-mono border-b border-slate-100 pb-1.5">
                            <th class="pb-1">Item Menu</th>
                            <th class="pb-1 text-center">Qty</th>
                            <th class="pb-1 text-right">Harga</th>
                            <th class="pb-1 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="py-2 pr-1">
                                    <div class="font-bold text-slate-900 leading-tight">{{ $item->product_name_snapshot }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->sku_snapshot }}</div>
                                </td>
                                <td class="py-2 text-center text-slate-700">{{ $item->quantity }}</td>
                                <td class="py-2 text-right text-slate-600">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-bold text-slate-900">{{ number_format($item->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Rincian Total -->
            <div class="py-3.5 border-b border-dashed border-slate-300 text-xs font-mono space-y-1.5">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Menu</span>
                    <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-100">
                    <span>TOTAL TAGIHAN</span>
                    <span class="text-emerald-700">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="pt-2 border-t border-slate-100 space-y-1">
                    <div class="flex justify-between text-slate-600">
                        <span>Metode Bayar</span>
                        <span class="font-bold uppercase text-slate-900">{{ $sale->payment?->methodLabel() ?? 'TUNAI' }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Struk -->
            <div class="text-center pt-5 space-y-1 text-xs text-slate-400">
                <p class="font-bold text-slate-700">Terima kasih atas pesanan Anda!</p>
                @if($sale->isPaid())
                    <p class="text-[11px] text-emerald-700 font-bold">Pesanan lunas & langsung diproses oleh Dapur.</p>
                @else
                    <p class="text-[11px] text-amber-800 font-bold">Tunjukkan struk ini ke kasir untuk bayar. Pesanan akan dimasak dapur setelah lunas.</p>
                @endif
                <p class="text-[11px] font-semibold text-slate-600">Hubungi Toko: {{ $storePhone }}</p>
            </div>
        </div>
    </div>

    <!-- Auto Countdown to Return to Kiosk Main Screen -->
    <script>
        let secondsLeft = 30;
        const countdownEl = document.getElementById('countdownTimer');
        const timer = setInterval(() => {
            secondsLeft--;
            if (countdownEl) countdownEl.innerText = secondsLeft;
            if (secondsLeft <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('kiosk.index') }}";
            }
        }, 1000);
    </script>
</body>
</html>

