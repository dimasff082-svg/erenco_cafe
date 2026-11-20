<!DOCTYPE html>
<html lang="id" class="h-full bg-[#151311]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Eranco Cafe & Resto</title>
    <link rel="icon" type="image/png" href="{{ $storeLogo }}?v={{ md5($storeLogo) }}">
    <link rel="shortcut icon" href="{{ $storeLogo }}?v={{ md5($storeLogo) }}">
    <link rel="apple-touch-icon" href="{{ $storeLogo }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-classic { font-family: 'Playfair Display', Georgia, serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-[#161412] text-[#f2ece1]">
    <div class="w-full max-w-lg text-center">
        <!-- Error Visual Icon -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 mb-6 shadow-lg shadow-rose-950/40">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <div class="bg-[#1f1c18]/90 backdrop-blur-xl border border-[#3b3428] rounded-2xl shadow-[0_15px_40px_-5px_rgba(0,0,0,0.65)] p-7 sm:p-9 text-left">
            <div class="text-center mb-6">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-rose-500/15 text-rose-400 border border-rose-500/30 mb-2">
                    Kode Kesalahan 403
                </span>
                <h1 class="text-2xl font-serif-classic font-bold text-[#f5ebd7] tracking-tight">Akses Halaman Dibatasi</h1>
                <p class="text-xs text-stone-400 mt-2 leading-relaxed">
                    {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses atau izin untuk membuka halaman ini.' }}
                </p>
            </div>

            @if(auth()->check())
                <div class="mb-6 p-4 rounded-xl bg-stone-900/60 border border-[#3b3428] text-xs space-y-1.5">
                    <p class="text-stone-400 font-medium uppercase tracking-wider text-[10px]">Informasi Sesi Login Saat Ini:</p>
                    <div class="flex justify-between items-center text-stone-200">
                        <span>Nama Pengguna:</span>
                        <span class="font-semibold text-[#f5ebd7]">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-200">
                        <span>Email:</span>
                        <span class="text-stone-300">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-200">
                        <span>Peran (Role):</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#d4af37]/20 text-[#d4af37] border border-[#d4af37]/30">
                            {{ auth()->user()->roleLabel() }}
                        </span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-stone-900 bg-gradient-to-r from-[#d4af37] to-[#e6ca65] hover:brightness-110 shadow-md transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Saya</span>
                </a>

                <a href="{{ route('logout') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-stone-300 bg-stone-800/80 hover:bg-stone-800 border border-stone-700/60 hover:text-white transition-all duration-200">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar / Ganti Akun</span>
                </a>
            </div>
        </div>

        <p class="text-[11px] uppercase font-cinzel tracking-[0.2em] text-stone-500 mt-6">
            Eranco Cafe & Resto &bull; Point Of Sales
        </p>
    </div>
</body>
</html>
