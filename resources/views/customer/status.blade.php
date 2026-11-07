<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Status Pesanan - Meja {{ $tableNumber }} ({{ $sale->transaction_number }}) | {{ $storeName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,800;1,600&display=swap" rel="stylesheet">
    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-resto { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="h-full flex flex-col justify-between text-slate-800 antialiased p-4 sm:p-6">

    <div class="max-w-md mx-auto w-full space-y-5">
        <!-- Top Nav Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-8 h-8 rounded-xl object-contain shadow-xs border border-amber-200 p-0.5 bg-white shrink-0" onerror="this.src='{{ asset('images/default-cake.png') }}'">
                <div class="leading-tight">
                    <span class="font-extrabold text-xs tracking-wider text-slate-900 block truncate">{{ $storeNameLine1 }}</span>
                    @if(!empty($storeNameLine2))
                        <span class="text-[10px] font-bold text-[#c59b27] block truncate">{{ $storeNameLine2 }}</span>
                    @endif
                </div>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">
                Meja {{ $tableNumber }}
            </span>
        </div>

        <!-- Order Card Status -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xl text-center space-y-5">
            @if($sale->isPaid())
                <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center shadow-md shadow-emerald-500/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            @else
                <div class="w-16 h-16 rounded-3xl bg-amber-100 text-amber-600 mx-auto flex items-center justify-center shadow-md shadow-amber-500/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            @endif

            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    {{ $sale->isPaid() ? 'Pesanan Lunas & Sedang Diproses' : 'Pesanan Berhasil Dikirim' }}
                </span>
                <h2 class="text-2xl font-black text-slate-900 font-mono mt-0.5">{{ $sale->transaction_number }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $sale->transaction_date->format('d/m/Y H:i') }} WIB &bull; Meja {{ $tableNumber }}
                </p>
            </div>

            <!-- Two Statuses: Dapur & Kasir -->
            <div class="grid grid-cols-2 gap-3 pt-2 text-left">
                <!-- Status Dapur -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Status Dapur</span>
                    @if($sale->order_status === 'completed')
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-emerald-700">
                            &check; Selesai Dimasak
                        </span>
                    @elseif($sale->order_status === 'processing')
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-blue-700 animate-pulse">
                            &bull; Sedang Dimasak
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-amber-700">
                            &bull; Menunggu Antrean
                        </span>
                    @endif
                </div>

                <!-- Status Pembayaran -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Status Pembayaran</span>
                    @if($sale->isPaid())
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-emerald-700">
                            &check; Lunas
                        </span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">({{ $sale->payment?->methodLabel() ?? 'Lunas' }})</span>
                    @elseif($sale->open_bill_id)
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-emerald-800">
                            &bull; Open Bill Meja
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Bayar di Kasir Nanti</span>
                    @else
                        <span class="inline-flex items-center gap-1 font-extrabold text-xs text-rose-700">
                            &bull; Belum Dibayar
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Online atau Kasir</span>
                    @endif
                </div>
            </div>

            <!-- Rincian Item Pesanan -->
            <div class="pt-4 border-t border-slate-100 text-left space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Rincian Menu:</span>
                <div class="divide-y divide-slate-100 max-h-48 overflow-y-auto text-xs">
                    @foreach($sale->items as $item)
                        <div class="py-2 flex items-center justify-between">
                            <span class="text-slate-800 font-medium">{{ $item->quantity }}x {{ $item->product_name_snapshot }}</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Tagihan:</span>
                    <span class="text-xl font-black text-slate-900">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Petunjuk & Tombol Pembayaran -->
            @if($sale->isPaid())
                <div class="bg-emerald-50 border border-emerald-200/80 p-4 rounded-2xl text-xs text-emerald-900 text-left leading-relaxed">
                    <div class="flex items-center gap-2 font-black text-emerald-800 mb-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Pembayaran Berhasil Diterima
                    </div>
                    Terima kasih! Pesanan Anda telah <strong>Lunas</strong> via {{ $sale->payment?->methodLabel() ?? 'Midtrans' }}. Tim dapur kami sedang menyiapkan hidangan lezat untuk Anda.
                </div>
            @elseif($sale->open_bill_id)
                <div class="bg-emerald-50/60 border border-emerald-300 p-4 rounded-2xl text-xs text-emerald-950 text-left leading-relaxed">
                    <span class="font-extrabold text-emerald-900 block mb-1">Tagihan Tergabung (Open Bill):</span>
                    Pesanan ini secara otomatis dimasukkan ke tagihan <strong>Open Bill Meja {{ $tableNumber }}</strong>. Anda dapat menikmati pesanan terlebih dahulu, dan melunasi seluruh tagihan sekaligus di kasir sebelum pulang.
                </div>
            @else
                <!-- Pilihan Pembayaran: Online Midtrans atau Kasir -->
                <div class="space-y-3">
                    @if($sale->snap_token)
                        <button type="button" onclick="payNow()" id="btnPaySnap"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-black shadow-lg shadow-amber-600/20 transition transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Bayar Sekarang via Midtrans (QRIS / E-Wallet)
                        </button>
                    @endif

                    <div class="bg-amber-50 p-4 rounded-2xl text-xs text-amber-900 text-left leading-relaxed border border-amber-200/60">
                        <span class="font-bold block mb-0.5">Opsi Bayar Tunai di Kasir:</span>
                        Jika ingin membayar secara tunai, silakan menuju ke meja kasir dan sebutkan <strong>Meja {{ $tableNumber }}</strong> atau No. Pesanan: <strong>{{ $sale->transaction_number }}</strong>.
                    </div>
                </div>
            @endif

            <!-- Tombol Navigasi Bawah -->
            <div class="space-y-2 pt-2">
                <button type="button" onclick="window.location.reload()"
                    class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Segarkan Status (Refresh)
                </button>

                <a href="{{ route('order.index', $tableNumber) }}"
                    class="w-full block py-3 rounded-2xl bg-slate-900 hover:bg-black text-white text-xs font-black shadow-md transition transform hover:scale-105 active:scale-95">
                    + Pesan Menu Tambahan
                </a>
            </div>
        </div>
    </div>

    @if($sale->isUnpaid() && $sale->snap_token)
    <script>
        function payNow() {
            if (!window.snap) {
                alert('Midtrans Snap SDK sedang dimuat, silakan coba beberapa detik lagi.');
                return;
            }

            window.snap.pay('{{ $sale->snap_token }}', {
                onSuccess: async function(result) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch('{{ route("order.midtrans_success", [$tableNumber, $sale->id]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                transaction_id: result.transaction_id || result.order_id,
                                result: result
                            })
                        });
                    } catch (err) {
                        console.error('Callback error:', err);
                    }
                    window.location.reload();
                },
                onPending: function(result) {
                    window.location.reload();
                },
                onError: function(result) {
                    alert('Pembayaran online belum berhasil: ' + (result.status_message || 'Silakan coba lagi atau bayar ke kasir.'));
                    window.location.reload();
                },
                onClose: function() {
                    // Penutupan popup tanpa bayar
                }
            });
        }
    </script>
    @endif

</body>
</html>
