<!DOCTYPE html>
<html lang="id" class="h-full bg-[#fbf9f4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Status Pesanan #{{ $sale->transaction_number }} - Fira Cake</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <script src="{{ $midtransSnapUrl ?? config('services.midtrans.snap_url') }}" data-client-key="{{ $midtransClientKey ?? config('services.midtrans.client_key') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-serif-classic {
            font-family: 'Playfair Display', Georgia, serif;
        }
    </style>
</head>
<body class="h-full flex flex-col text-[#26211a] bg-[#fbf9f4] antialiased selection:bg-[#c59b27] selection:text-white">

    <div class="w-full max-w-md mx-auto min-h-screen flex flex-col bg-white shadow-xl relative border-x border-[#ede7dc]">

        <!-- Top Header -->
        <header class="bg-white border-b border-[#eee7dc] px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-9 h-9 rounded-xl object-contain shadow-2xs border border-[#eee7dc] p-0.5 bg-[#fdfbf7]">
                <div>
                    <h1 class="text-sm font-black text-[#26211a] leading-tight">Fira Cake</h1>
                    <p class="text-[10px] text-[#786e60]">Struk & Status Pesanan Online</p>
                </div>
            </div>
            <a href="{{ route('online_order.index') }}" class="text-[11px] font-bold text-[#c59b27] hover:underline">
                Pesan Menu Lain &rarr;
            </a>
        </header>

        <main class="flex-1 p-5 space-y-4">
            
            @if($sale->isUnpaid())
                <!-- Unpaid Pending Card -->
                <div class="bg-gradient-to-br from-amber-50 via-amber-100/40 to-amber-50 border border-amber-300 rounded-3xl p-5 text-center shadow-xs space-y-2">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-500 text-stone-950 flex items-center justify-center shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-200 text-amber-900 mb-1">
                            Menunggu Pembayaran Midtrans
                        </span>
                        <h2 class="font-serif-classic text-xl font-bold text-amber-950">Selesaikan Pembayaran</h2>
                        <p class="text-xs text-amber-900/80 leading-relaxed mt-1">
                            Halo <strong>{{ $sale->customer_name }}</strong>! Pesanan Anda telah dibuat. Silakan selesaikan pembayaran via Midtrans agar pesanan segera diteruskan ke dapur & kasir.
                        </p>
                    </div>
                    <div class="pt-2">
                        <button type="button" onclick="payWithMidtrans()" id="btnPaySnap"
                            class="w-full py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Bayar Sekarang via Midtrans</span>
                        </button>
                    </div>
                </div>
            @else
                <!-- Success Status Card -->
                <div class="bg-gradient-to-br from-emerald-50 via-emerald-100/50 to-emerald-50 border border-emerald-300 rounded-3xl p-5 text-center shadow-xs space-y-2">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-200 text-emerald-900 mb-1">
                            Pembayaran Lunas via Midtrans
                        </span>
                        <h2 class="font-serif-classic text-xl font-bold text-emerald-950">Pesanan Online Diterima!</h2>
                        <p class="text-xs text-emerald-900/80 leading-relaxed mt-1">
                            Terima kasih, <strong>{{ $sale->customer_name }}</strong>! Pesanan Anda telah lunas via Midtrans Payment Gateway dan langsung diteruskan ke kasir & dapur Fira Cake.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Order Progress Stepper -->
            <div class="bg-[#faf7f0] border border-[#e5dfd3] rounded-2xl p-4 space-y-3">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#786e60]">Tahapan Status Pesanan</div>
                <div class="relative pl-6 space-y-4 border-l-2 {{ $sale->isPaid() ? 'border-emerald-400' : 'border-amber-400' }} ml-2">
                    
                    <!-- Step 1: Lunas / Menunggu -->
                    <div class="relative">
                        @if($sale->isPaid())
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shadow-xs">
                                ✓
                            </span>
                            <div class="text-xs font-bold text-[#26211a]">Pembayaran Terverifikasi (Lunas)</div>
                            <div class="text-[10px] text-[#786e60]">Midtrans Payment Gateway &bull; {{ ($sale->payment?->paid_at ?? $sale->transaction_date)->format('d/m/Y H:i') }} WIB</div>
                        @else
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-amber-500 text-stone-950 flex items-center justify-center text-[9px] font-bold shadow-xs animate-pulse">
                                !
                            </span>
                            <div class="text-xs font-bold text-amber-900">Menunggu Pembayaran Midtrans</div>
                            <div class="text-[10px] text-amber-700">Silakan selesaikan pembayaran untuk memproses pesanan</div>
                        @endif
                    </div>

                    <!-- Step 2: Dapur -->
                    <div class="relative">
                        @if($sale->order_status === 'processing' || $sale->order_status === 'completed')
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shadow-xs">
                                ✓
                            </span>
                            <div class="text-xs font-bold text-emerald-900">Sedang Disiapkan Dapur</div>
                        @elseif($sale->isPaid())
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-amber-500 text-stone-900 flex items-center justify-center text-[9px] font-bold shadow-xs">
                                &bull;
                            </span>
                            <div class="text-xs font-bold text-[#26211a]">Menunggu Antrean Dapur & Kasir</div>
                        @else
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-stone-300 text-stone-600 flex items-center justify-center text-[9px] font-bold">
                                2
                            </span>
                            <div class="text-xs font-bold text-stone-400">Menunggu Pembayaran Selesai</div>
                        @endif
                        <div class="text-[10px] text-[#786e60]">Kue & hidangan diproses higienis dan fresh</div>
                    </div>

                    <!-- Step 3: Siap -->
                    <div class="relative">
                        @if($sale->order_status === 'completed')
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shadow-xs">
                                ✓
                            </span>
                            <div class="text-xs font-bold text-emerald-900">Pesanan Siap Diambil / Diantar</div>
                        @else
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-stone-300 text-stone-600 flex items-center justify-center text-[9px] font-bold">
                                3
                            </span>
                            <div class="text-xs font-bold text-stone-400">Siap Diambil / Diantar</div>
                        @endif
                        <div class="text-[10px] text-[#786e60]">Kasir akan mengabari Anda via WhatsApp</div>
                    </div>
                </div>
            </div>

            <!-- Receipt Card -->
            <div class="bg-white border border-[#e5dfd3] rounded-2xl p-4 space-y-3 shadow-2xs text-xs">
                <div class="flex items-center justify-between pb-2.5 border-b border-[#f0ebd9]">
                    <div>
                        <span class="text-[10px] text-[#8c8270] block">Nomor Transaksi</span>
                        <span class="font-mono font-black text-sm text-[#26211a]">{{ $sale->transaction_number }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-[#8c8270] block">Waktu Pesan</span>
                        <span class="text-xs font-semibold text-[#5c5344]">{{ $sale->transaction_date->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <!-- Data Pelanggan -->
                <div class="bg-[#faf7f0] p-2.5 rounded-xl border border-[#ede7dc] space-y-1">
                    <div class="flex justify-between">
                        <span class="text-[#786e60]">Nama Pemesan:</span>
                        <strong class="text-[#26211a]">{{ $sale->customer_name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786e60]">No. WhatsApp:</span>
                        <strong class="font-mono text-[#26211a]">{{ $sale->customer_phone ?? '-' }}</strong>
                    </div>
                    @if($sale->notes)
                        <div class="pt-1 border-t border-[#e8dfcf] text-[11px] text-[#6b6252]">
                            {{ $sale->notes }}
                        </div>
                    @endif
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-2 pt-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#786e60]">Daftar Menu</div>
                    <div class="divide-y divide-[#f0ebd9]">
                        @foreach($sale->items as $item)
                            <div class="py-2 flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-[#26211a] truncate">{{ $item->product_name_snapshot }}</div>
                                    <div class="text-[10px] text-[#786e60]">
                                        {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-bold text-[#26211a] text-right">
                                    Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="pt-2 border-t border-[#f0ebd9] space-y-1">
                    <div class="flex justify-between text-[#786e60]">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[#786e60]">
                        <span>Metode Bayar</span>
                        <span class="font-bold text-[#26211a]">Midtrans Payment Gateway</span>
                    </div>
                    <div class="pt-2 border-t border-[#e5dfd3] flex justify-between items-center">
                        <span class="font-bold text-[#26211a]">Total Tagihan:</span>
                        <span class="font-serif-classic font-black text-base {{ $sale->isPaid() ? 'text-emerald-800' : 'text-amber-800' }}">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            @if($sale->isPaid())
                                <span class="text-[11px] font-sans font-bold text-emerald-700 ml-1">(Lunas)</span>
                            @else
                                <span class="text-[11px] font-sans font-bold text-amber-700 ml-1">(Belum Bayar)</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Kasir Action Button -->
            <div class="space-y-2 pt-2">
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Halo Kasir Fira Cake, saya ingin menanyakan status pesanan Online saya #' . $sale->transaction_number . ' atas nama ' . $sale->customer_name . '.') }}" target="_blank"
                    class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-1.114-.072-.258-.083-.588-.204-1.013-.39-.884-.388-1.554-1.045-2.002-1.637-.091-.12-.349-.47-.349-.896 0-.426.223-.636.303-.717.08-.08.175-.101.233-.101.059 0 .118.001.17.004.055.003.128-.02.2.152.073.175.25.612.272.656.022.045.037.098.007.158-.03.06-.045.097-.09.15-.045.053-.095.118-.135.158-.045.045-.092.094-.039.185.053.09.236.388.506.629.349.311.644.408.734.453.09.045.143.038.196-.023.053-.06.226-.263.286-.353.06-.09.12-.075.203-.045.083.03.526.248.616.293.09.045.15.068.173.105.022.038.022.218-.122.623z"/>
                    </svg>
                    <span>Hubungi Toko via WhatsApp</span>
                </a>

                <a href="{{ route('online_order.index') }}"
                    class="w-full py-2.5 rounded-2xl bg-[#faf7f0] hover:bg-[#ede7da] text-[#5c5344] font-bold text-xs border border-[#dcd4c5] transition flex items-center justify-center gap-1.5">
                    <span>Kembali ke Halaman Menu</span>
                </a>
            </div>

        </main>
    </div>

    @if($sale->isUnpaid())
    <script>
        async function payWithMidtrans() {
            const btn = document.getElementById('btnPaySnap');
            btn.disabled = true;
            btn.innerText = 'Membuka Midtrans...';

            let snapToken = "{{ $sale->snap_token }}";

            if (!snapToken) {
                try {
                    const res = await fetch("{{ route('online_order.snap_token', $sale->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const d = await res.json();
                    if (d.snap_token) {
                        snapToken = d.snap_token;
                    }
                } catch(e) {}
            }

            if (snapToken && window.snap) {
                window.snap.pay(snapToken, {
                    onSuccess: async function(result) {
                        try {
                            await fetch("{{ route('online_order.midtrans_success', $sale->id) }}", {
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
                        window.location.reload();
                    },
                    onPending: function(result) {
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert('Pembayaran Midtrans gagal atau dibatalkan.');
                        btn.disabled = false;
                        btn.innerText = 'Bayar Sekarang via Midtrans';
                    },
                    onClose: function() {
                        btn.disabled = false;
                        btn.innerText = 'Bayar Sekarang via Midtrans';
                    }
                });
            } else {
                alert('Midtrans Snap SDK sedang tidak aktif atau token belum siap. Silakan refresh halaman.');
                btn.disabled = false;
                btn.innerText = 'Bayar Sekarang via Midtrans';
            }
        }
    </script>
    @endif

</body>
</html>
