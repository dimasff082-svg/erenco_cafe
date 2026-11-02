<!DOCTYPE html>
<html lang="id" class="h-full bg-[#151311]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Point of Sale (Modern Classic)</title>
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
    <div class="w-full max-w-md">
        <!-- Brand Header: Fira Cake Official Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-24 h-24 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-300">
            </div>
            <h1 class="text-2xl font-serif-classic font-bold tracking-tight text-[#f5ebd7]">FIRA CAKE</h1>
            <p class="text-xs uppercase font-cinzel tracking-[0.25em] text-[#c59b27] mt-1">BAKERY & PASTRY</p>
            <p class="text-xs uppercase font-cinzel tracking-[0.25em] text-[#c59b27] mt-1">Point Of Sales Terintegrasi V 1.2 LJ</p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#1f1c18]/90 backdrop-blur-xl border border-[#3b3428] rounded-2xl shadow-[0_15px_40px_-5px_rgba(0,0,0,0.65)] p-7 sm:p-8">
            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4.5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-[#c7bfb1] mb-1.5">Email Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8c8270]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@pos.test"
                            class="w-full bg-[#161412] border border-[#3d362b] rounded-xl pl-10 pr-4 py-2.5 text-sm text-[#f5ebd7] placeholder-[#665e52] focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#c7bfb1] mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8c8270]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            placeholder="••••••••"
                            class="w-full bg-[#161412] border border-[#3d362b] rounded-xl pl-10 pr-4 py-2.5 text-sm text-[#f5ebd7] placeholder-[#665e52] focus:outline-none focus:ring-2 focus:ring-[#c59b27] focus:border-transparent transition">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer text-[#8c8270] hover:text-[#c7bfb1]">
                        <input type="checkbox" name="remember" class="rounded border-[#3d362b] bg-[#161412] text-[#c59b27] focus:ring-[#c59b27]">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" id="btn-submit"
                    class="w-full mt-2 bg-[#c59b27] hover:bg-[#a98119] text-stone-950 font-serif-classic font-bold py-3 px-4 rounded-xl shadow-lg shadow-black/40 transition transform active:scale-98 flex items-center justify-center gap-2 text-sm border border-[#e5be58]/50">
                    <span>Masuk ke Sistem</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-[#736858] mt-6">
            &copy; {{ date('Y') }} Point of Sale &bull; Modern Classic Edition
        </p>
        <p class="text-center text-xs text-[#736858] mt-6">
            <a href="https://linyjayainformatika.co.id" target="_blank" rel="noopener noreferrer">Develop by PT. Liny Jaya Informatika</a>
        </p>
    </div>
</body>
</html>
