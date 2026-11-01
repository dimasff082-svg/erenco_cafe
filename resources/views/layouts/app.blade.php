<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f6f4ee]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS App' }} - Point of Sale</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        [x-cloak] { display: none !important; }
        .shortcut-key {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            background-color: #ffffff;
            border: 1px solid #dcd4c5;
            color: #262017;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
            display: inline-block;
            line-height: 1.2;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col antialiased text-[#2c261e] bg-[#f7f5ef]">
    <div class="min-h-full flex relative">
        <!-- Sidebar Navigation (Classic Modern Theme) -->
        <aside id="app-sidebar" class="w-64 bg-[#191714] text-[#d6cfc2] flex flex-col shrink-0 border-r border-[#2b261f] shadow-2xl transition-all duration-300 ease-in-out z-40">
            <!-- Brand Header: Fira Cake Logo -->
            <div class="h-16 flex items-center px-5 gap-3 bg-[#12110e] border-b border-[#2b261f]">
                <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-9 h-9 object-contain drop-shadow-md shrink-0">
                <div class="overflow-hidden">
                    <span class="font-serif-classic font-bold text-sm tracking-wide text-[#f2ece1] block truncate">Fira Cake</span>
                    <span class="text-[9px] tracking-widest uppercase font-semibold text-[#c59b27] block">Bakery & Pastry</span>
                </div>
            </div>

            <!-- User Info Badge -->
            <div class="p-3.5 mx-3 my-3 rounded-2xl bg-[#23201b] border border-[#383228] flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ auth()->user()->isOwner() ? 'from-amber-600 to-amber-800 border border-amber-400/40 text-amber-100' : (auth()->user()->isAdmin() ? 'from-stone-700 to-stone-900 border border-stone-500/30 text-stone-100' : (auth()->user()->isKitchen() ? 'from-blue-700 to-indigo-900 border border-blue-500/30 text-blue-100' : 'from-emerald-700 to-emerald-900 border border-emerald-500/30 text-emerald-100')) }} flex items-center justify-center font-serif-classic font-bold text-base shadow">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-[#f2ece1] truncate">{{ auth()->user()->name }}</p>
                    <span class="inline-flex items-center gap-1.5 text-[9px] font-bold tracking-wider px-2 py-0.5 rounded-full {{ auth()->user()->isOwner() ? 'bg-amber-500/20 text-[#eac36c] border border-amber-500/30' : (auth()->user()->isAdmin() ? 'bg-stone-500/20 text-stone-300 border border-stone-600/30' : (auth()->user()->isKitchen() ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30')) }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->isOwner() ? 'bg-amber-400' : (auth()->user()->isAdmin() ? 'bg-stone-400' : (auth()->user()->isKitchen() ? 'bg-blue-400' : 'bg-emerald-400')) }}"></span>
                        {{ auth()->user()->isOwner() ? 'PEMILIK TOKO' : (auth()->user()->isAdmin() ? 'ADMINISTRATOR' : (auth()->user()->isKitchen() ? 'STAF DAPUR' : 'KASIR')) }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 space-y-1 overflow-y-auto py-2 text-xs font-medium">
                @if(auth()->user()->isOwner())
                    <!-- Owner Navigation (Pemilik) -->
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Menu Eksekutif</div>
                    <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('owner.dashboard') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md shadow-amber-950/30' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard Pemilik
                    </a>
                    <a href="{{ route('owner.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('owner.reports.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md shadow-amber-950/30' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Laporan Penjualan
                    </a>
                    <a href="{{ route('owner.transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('owner.transactions.*') || request()->routeIs('admin.transactions.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md shadow-amber-950/30' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Riwayat Transaksi
                    </a>

                @elseif(auth()->user()->isKitchen())
                    <!-- Kitchen Staff Navigation -->
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Dapur Resto</div>
                    <a href="{{ route('kitchen.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('kitchen.*') ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Layar Dapur (KDS)
                    </a>

                @elseif(auth()->user()->isAdmin())
                    <!-- Admin Navigation -->
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Utama</div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard Ringkasan
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Operasional Resto</div>
                    <a href="{{ route('takeaway.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('takeaway.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <span class="text-base leading-none">🛍️</span>
                        transaksi penjualan
                    </a>
                    <a href="{{ route('cashier.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.dashboard') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Pesanan Masuk (Kasir)
                    </a>
                    <a href="{{ route('cashier.open_bills.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.open_bills.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Open Bill Meja
                    </a>
                    <a href="{{ route('admin.tables.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.tables.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 9a2 2 0 012 2v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5a2 2 0 012-2m0 0h8" />
                        </svg>
                        Data Meja & QR Code
                    </a>
                    <a href="{{ route('kitchen.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('kitchen.*') ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Layar Dapur (KDS)
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Master Menu</div>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.categories.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Kategori Menu
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.products.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Data Menu
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Audit & Penjualan</div>
                    <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.transactions.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Riwayat Transaksi
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Laporan Penjualan
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Pengaturan</div>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Manajemen Pengguna
                    </a>

                @else
                    <!-- Cashier Navigation: Operasional Pesanan, Open Bill, & Riwayat Transaksi -->
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Operasional Resto</div>
                    <a href="{{ route('takeaway.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('takeaway.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <span class="text-base leading-none">🛍️</span>
                        Pesanan Bawa Pulang (Takeaway)
                    </a>
                    <a href="{{ route('cashier.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.dashboard') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Pesanan Masuk (Kasir)
                    </a>
                    <a href="{{ route('cashier.open_bills.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.open_bills.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Kelola Open Bill Meja
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Katalog Menu</div>
                    <a href="{{ route('cashier.menu.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.menu.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Data Menu (Katalog)
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-[#8c8270]">Audit & Penjualan</div>
                    <a href="{{ route('cashier.transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cashier.transactions.*') ? 'bg-[#c59b27] text-stone-950 font-bold shadow-md' : 'text-[#c7bfb1] hover:bg-[#25221c] hover:text-[#f3eee5]' }}">
                        <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Riwayat Transaksi Lunas
                    </a>
                @endif
            </nav>

            <!-- Logout Section -->
            <div class="p-3 border-t border-[#2b261f]">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-300 hover:bg-rose-500/15 hover:text-rose-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Backdrop Overlay for Sidebar -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-stone-900/60 z-30 md:hidden backdrop-blur-2xs transition-opacity"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#f7f5ef]">
            <!-- Topbar Header (Classic Modern with Shopping Bag Logo + Point of Sale text, NO Kasir Baru button) -->
            <header class="h-16 bg-[#ffffff]/95 backdrop-blur-md border-b border-[#e5dfd3] flex items-center justify-between px-6 sm:px-8 sticky top-0 z-20 shadow-[0_2px_12px_-4px_rgba(40,30,15,0.05)]">
                <div class="flex items-center gap-3">
                    <!-- Toggle Sidebar Button (Admin, Kasir, Pemilik, Dapur) -->
                    <button type="button" id="sidebar-toggle-btn" onclick="toggleSidebar()"
                        class="w-9 h-9 rounded-xl bg-[#faf7f0] hover:bg-[#ede7da] text-[#3d3429] flex items-center justify-center border border-[#dcd4c5] transition shadow-2xs hover:scale-105 active:scale-95 focus:outline-none"
                        title="Buka / Tutup Sidebar (Alt + B)" aria-label="Toggle Sidebar">
                        <svg class="w-5 h-5 text-[#3d3429]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Fira Cake Logo -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="Fira Cake" class="w-9 h-9 object-contain drop-shadow-sm shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-serif-classic font-extrabold text-lg text-[#26211a] tracking-tight">Fira Cake</span>
                            <span class="text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded-full bg-[#f4ece0] text-[#8a681c] border border-[#dfb15b]/30">Resto & Bakery</span>
                        </div>
                        <p class="text-[11px] text-[#7d7363] font-medium leading-tight">{{ $header ?? 'Panel Kasir Toko' }}</p>
                    </div>
                </div> -->

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <span class="text-xs text-[#8c806e] block font-serif-classic">{{ now()->translatedFormat('l, d F Y') }}</span>
                        <span class="text-xs font-bold text-[#3d3429] tracking-wider" id="live-clock"></span>
                    </div>
                    <!-- 'Kasir Baru' button removed completely per instruction -->
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-8 pt-4">
                @if(session('success'))
                    <div class="flex items-center gap-3 p-4 mb-3 text-xs text-emerald-950 rounded-2xl bg-emerald-50/90 border border-emerald-300/80 shadow-xs">
                        <svg class="w-5 h-5 shrink-0 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="flex items-center gap-3 p-4 mb-3 text-xs text-rose-950 rounded-2xl bg-rose-50/90 border border-rose-300/80 shadow-xs">
                        <svg class="w-5 h-5 shrink-0 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 mb-3 text-xs text-rose-950 rounded-2xl bg-rose-50/90 border border-rose-300/80 shadow-xs">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <svg class="w-5 h-5 shrink-0 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Terdapat beberapa kesalahan:
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 p-8 overflow-y-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Keyboard Shortcut Help Modal (Modern Classic) -->
    <div id="global-shortcut-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-3xl border border-[#e5dfd3] shadow-2xl p-6 sm:p-7 space-y-5">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3.5 border-b border-[#f0ebd9]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#c59b27] to-[#73550b] flex items-center justify-center text-stone-950 shadow-md">
                        <i class="fa-regular fa-keyboard text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-serif-classic font-bold text-lg text-[#262017]">Daftar Shortcut Keyboard</h3>
                        <p class="text-xs text-[#786e60]">Gunakan tombol cepat keyboard untuk mempercepat transaksi kasir & navigasi sistem.</p>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalShortcutModal()" class="w-8 h-8 rounded-xl bg-[#faf7f0] hover:bg-stone-200 text-[#786e60] hover:text-[#262017] flex items-center justify-center transition border border-[#e5dfd3] cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Shortcuts Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Group 1: Kasir & POS -->
                <div class="bg-[#faf7f0] p-4 rounded-2xl border border-[#eee7d8] space-y-2.5">
                    <div class="flex items-center gap-2 font-serif-classic font-bold text-xs uppercase tracking-wider text-[#8a681c] pb-1.5 border-b border-[#e5dfd3]">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Transaksi Kasir (POS)</span>
                    </div>
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Cari Produk / Barcode</span>
                            <div class="flex gap-1"><kbd class="shortcut-key">F1</kbd> <span class="text-stone-400">/</span> <kbd class="shortcut-key">/</kbd></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Input Uang Tunai</span>
                            <kbd class="shortcut-key">F2</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Pilih Uang Pas</span>
                            <kbd class="shortcut-key">F4</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Input Diskon Transaksi</span>
                            <kbd class="shortcut-key">F7</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Selesai Pembayaran & Struk</span>
                            <kbd class="shortcut-key font-bold text-emerald-800">Enter</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#4d4437] font-medium">Kosongkan Keranjang</span>
                            <kbd class="shortcut-key">Alt+C</kbd>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Metode Bayar & Navigasi -->
                <div class="space-y-4">
                    <div class="bg-[#faf7f0] p-4 rounded-2xl border border-[#eee7d8] space-y-2.5">
                        <div class="flex items-center gap-2 font-serif-classic font-bold text-xs uppercase tracking-wider text-[#1b4332] pb-1.5 border-b border-[#e5dfd3]">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Pilihan Pembayaran</span>
                        </div>
                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Metode Tunai (Cash)</span>
                                <kbd class="shortcut-key">Alt+1</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Metode Transfer Bank</span>
                                <kbd class="shortcut-key">Alt+2</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Metode QRIS</span>
                                <kbd class="shortcut-key">Alt+3</kbd>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#faf7f0] p-4 rounded-2xl border border-[#eee7d8] space-y-2.5">
                        <div class="flex items-center gap-2 font-serif-classic font-bold text-xs uppercase tracking-wider text-[#3d3429] pb-1.5 border-b border-[#e5dfd3]">
                            <i class="fa-solid fa-compass"></i>
                            <span>Navigasi & Bantuan</span>
                        </div>
                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Buka Panduan Shortcut</span>
                                <div class="flex gap-1"><kbd class="shortcut-key">F9</kbd> <span class="text-stone-400">/</span> <kbd class="shortcut-key">?</kbd></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Buka Dashboard</span>
                                <kbd class="shortcut-key">Alt+D</kbd>
                            </div>
                            @if(auth()->user()->isCashier())
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Buka Transaksi POS</span>
                                <kbd class="shortcut-key">Alt+K</kbd>
                            </div>
                            @endif
                            @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Buka Laporan Penjualan</span>
                                <kbd class="shortcut-key">Alt+L</kbd>
                            </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Buka / Tutup Sidebar</span>
                                <kbd class="shortcut-key">Alt+B</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#4d4437] font-medium">Tutup Modal / Batal</span>
                                <kbd class="shortcut-key">Esc</kbd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-[#f0ebd9] flex items-center justify-between text-xs text-[#786e60]">
                <span>Tekan <kbd class="shortcut-key">Esc</kbd> untuk menutup jendela panduan</span>
                <button type="button" onclick="closeGlobalShortcutModal()" class="px-5 py-2 rounded-xl bg-[#262017] hover:bg-[#3d3324] text-white font-bold transition shadow-xs cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar) return;

            const isCollapsed = sidebar.classList.contains('-ml-64');
            if (isCollapsed) {
                sidebar.classList.remove('-ml-64');
                if (backdrop && window.innerWidth < 768) {
                    backdrop.classList.remove('hidden');
                }
                localStorage.setItem('sidebar_collapsed', 'false');
            } else {
                sidebar.classList.add('-ml-64');
                if (backdrop) {
                    backdrop.classList.add('hidden');
                }
                localStorage.setItem('sidebar_collapsed', 'true');
            }
        }

        // Restore saved sidebar state
        (function initSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar) return;

            const saved = localStorage.getItem('sidebar_collapsed');
            if (window.innerWidth < 768) {
                sidebar.classList.add('-ml-64');
                if (backdrop) backdrop.classList.add('hidden');
            } else if (saved === 'true') {
                sidebar.classList.add('-ml-64');
            }
        })();

        function updateClock() {
            const el = document.getElementById('live-clock');
            if (el) {
                const now = new Date();
                el.innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        function openGlobalShortcutModal() {
            const modal = document.getElementById('global-shortcut-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeGlobalShortcutModal() {
            const modal = document.getElementById('global-shortcut-modal');
            if (modal) modal.classList.add('hidden');
        }

        // Close on backdrop click
        document.getElementById('global-shortcut-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGlobalShortcutModal();
            }
        });

        // Global Keyboard Event Listener
        window.addEventListener('keydown', function(e) {
            const activeTag = document.activeElement ? document.activeElement.tagName : '';
            const isTyping = activeTag === 'INPUT' || activeTag === 'TEXTAREA';

            // F9: Toggle Shortcut Modal
            if (e.key === 'F9') {
                e.preventDefault();
                const modal = document.getElementById('global-shortcut-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeGlobalShortcutModal();
                } else {
                    openGlobalShortcutModal();
                }
                return;
            }

            // '?' (Shift + /): Open Shortcut Modal when not typing
            if (e.key === '?' && !isTyping) {
                e.preventDefault();
                openGlobalShortcutModal();
                return;
            }

            // Escape: Close Modal if open
            if (e.key === 'Escape') {
                const modal = document.getElementById('global-shortcut-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    e.preventDefault();
                    closeGlobalShortcutModal();
                    return;
                }
            }

            // Alt + B: Toggle Sidebar
            if (e.altKey && (e.key === 'b' || e.key === 'B')) {
                e.preventDefault();
                toggleSidebar();
                return;
            }

            // Alt + D: Navigate to Dashboard
            if (e.altKey && (e.key === 'd' || e.key === 'D')) {
                e.preventDefault();
                @if(auth()->user()->isOwner())
                    window.location.href = "{{ route('owner.dashboard') }}";
                @elseif(auth()->user()->isAdmin())
                    window.location.href = "{{ route('admin.dashboard') }}";
                @else
                    window.location.href = "{{ route('cashier.dashboard') }}";
                @endif
                return;
            }

            @if(auth()->user()->isCashier())
            // Alt + K: Navigate to POS Cashier
            if (e.altKey && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                window.location.href = "{{ route('pos.index') }}";
                return;
            }
            @endif

            @if(auth()->user()->isOwner())
            // Alt + L: Navigate to Owner Reports
            if (e.altKey && (e.key === 'l' || e.key === 'L')) {
                e.preventDefault();
                window.location.href = "{{ route('owner.reports.index') }}";
                return;
            }
            @elseif(auth()->user()->isAdmin())
            // Alt + L: Navigate to Admin Reports
            if (e.altKey && (e.key === 'l' || e.key === 'L')) {
                e.preventDefault();
                window.location.href = "{{ route('admin.reports.index') }}";
                return;
            }
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
