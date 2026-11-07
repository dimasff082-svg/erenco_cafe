<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - Meja {{ $table->table_number }} | {{ $storeName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f7f5ef;
        }
        .font-serif-classic {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .font-cinzel {
            font-family: 'Cinzel', serif;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #262017 !important;
                margin: 0 auto !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 text-stone-900">

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print max-w-sm w-full mb-4 flex items-center justify-between">
        <a href="{{ route('admin.tables.index') }}" class="text-xs font-bold text-amber-900 hover:text-amber-950 flex items-center gap-1.5 bg-amber-100 hover:bg-amber-200 px-3 py-2 rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>

        <button type="button" onclick="window.print()" class="text-xs font-bold text-white bg-[#262017] hover:bg-[#12110e] flex items-center gap-1.5 px-4 py-2 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Stiker Meja (Print)
        </button>
    </div>

    <!-- Printable Table Standee Card -->
    <div class="print-card bg-white rounded-3xl border-2 border-[#262017] p-8 max-w-sm w-full shadow-2xl text-center flex flex-col items-center justify-between relative overflow-hidden">
        
        <!-- Header Brand -->
        <div class="space-y-1 w-full flex flex-col items-center">
            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-14 h-14 object-contain mx-auto drop-shadow-md mb-1" onerror="this.src='{{ asset('images/default-cake.png') }}'">
            <h1 class="font-cinzel text-sm tracking-[0.2em] text-[#262017] font-bold uppercase leading-tight">
                <span>{{ $storeNameLine1 }}</span>
                @if(!empty($storeNameLine2))
                    <span class="block text-xs font-semibold text-[#8a681c] tracking-[0.15em] mt-0.5">{{ $storeNameLine2 }}</span>
                @endif
            </h1>
            <p class="text-[11px] text-stone-500 font-medium tracking-wide">Pemesanan Mandiri Pelanggan</p>
        </div>

        <!-- Table Identity Badge -->
        <div class="my-5 w-full bg-[#faf7f0] rounded-2xl border border-[#e5dfd3] p-3.5">
            <span class="text-[10px] font-bold uppercase tracking-widest text-[#8a681c] block font-serif-classic">Nomor Meja</span>
            <div class="text-3xl font-black font-mono tracking-tight text-[#262017] mt-0.5">
                {{ $table->table_number }}
            </div>
            <p class="text-xs text-stone-600 font-semibold mt-0.5">{{ $table->name }}</p>
        </div>

        <!-- QR Code Container -->
        <div class="p-4 bg-white rounded-2xl border-2 border-dashed border-stone-300 shadow-inner my-2 flex items-center justify-center">
            <div id="qrcode-wrapper" class="flex items-center justify-center">
                <!-- Fallback QR Code image rendered via SVG/API and Client QR Code Generator -->
                <img id="qr-image" 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($orderUrl) }}&margin=0" 
                    alt="QR Code Meja {{ $table->table_number }}" 
                    class="w-52 h-52 object-contain"
                    onerror="generateLocalQr()">
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-4 space-y-1 text-center">
            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#262017] uppercase tracking-wide">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                Scan Barcode / QR Meja
            </span>
            <p class="text-[11px] text-stone-500 leading-snug max-w-xs mx-auto">
                Buka kamera smartphone Anda dan arahkan ke barcode ini untuk memilih menu dan mengirim pesanan langsung ke Dapur.
            </p>
        </div>

        <!-- URL Text Link -->
        <div class="mt-4 pt-3 border-t border-stone-200 w-full text-[10px] text-stone-400 font-mono truncate">
            {{ $orderUrl }}
        </div>
    </div>

    <!-- Client-side Lightweight QR Generator Script for 100% Offline Resilience -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            try {
                if (typeof QRCode !== 'undefined') {
                    const wrapper = document.getElementById('qrcode-wrapper');
                    wrapper.innerHTML = '';
                    new QRCode(wrapper, {
                        text: @json($orderUrl),
                        width: 210,
                        height: 210,
                        colorDark: "#191714",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            } catch (e) {
                console.log('Using default QR Image', e);
            }
        });

        function generateLocalQr() {
            // Already handled by QRCode library fallback
        }
    </script>
</body>
</html>

