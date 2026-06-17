<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Logista - Warehouse Control Dashboard')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* ========================================
           SIDEBAR MOBILE - DRAWER
           ======================================== */
        .sidebar-mobile {
            position: fixed;
            top: 0;
            left: 0;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
            width: 260px;
            height: 100vh;
            background-color: #1c1515;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        
        .sidebar-mobile.open {
            transform: translateX(0);
        }
        
        /* OVERLAY */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
            backdrop-filter: blur(2px);
        }
        
        .overlay.active {
            display: block;
        }
        
        /* ========================================
           PROFIL DROPDOWN
           ======================================== */
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #1c1515;
            border: 1px solid #4d3535;
            border-radius: 12px;
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            z-index: 1000;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: #e2e8f0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: #2b2020;
            color: white;
        }
        
        .dropdown-logout {
            color: #f87171;
        }
        
        .dropdown-logout:hover {
            background: #3b1e1e;
            color: #ef4444;
        }
        
        .dropdown-item .material-symbols-outlined {
            font-size: 18px;
        }
        
        /* ========================================
           SCROLL LOCK
           ======================================== */
        body.no-scroll {
            overflow: hidden;
        }
        
        /* ========================================
           CUSTOM SCROLLBAR
           ======================================== */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #2a1f1f;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #5c3a3a;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #7a4d4d;
        }
    </style>
</head>

<body class="bg-[#120e0e] text-on-surface flex min-h-screen">
    
    <!-- OVERLAY untuk mobile sidebar -->
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>
    
    <!-- SIDEBAR MOBILE (Drawer) -->
    <aside id="mobileSidebar" class="sidebar-mobile">
        <div class="flex justify-between items-center p-md border-b border-[#4d3535]/40">
            <div>
                <h1 class="font-display text-headline-sm font-bold text-primary tracking-tight">Logista</h1>
                <p class="font-body-sm text-on-surface-variant opacity-70">Warehouse Control</p>
            </div>
            <button onclick="closeSidebar()" class="text-on-surface-variant hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <nav class="flex-1 p-md space-y-1">
            <a href="/dashboard" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('dashboard') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-body-md">Dashboard</span>
            </a>
            <a href="/items" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('items*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="font-body-md">Data Barang</span>
            </a>
            <a href="/ops" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('ops*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">swap_horiz</span>
                <span class="font-body-md">Transaksi Stok</span>
            </a>
            <a href="/audit-trail" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('audit-trail*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">history</span>
                <span class="font-body-md">Riwayat Aktivitas</span>
            </a>
            <a href="/reports" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('reports*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">analytics</span>
                <span class="font-body-md">Laporan</span>
            </a>
            <a href="/alerts" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('alerts*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">notifications_active</span>
                <span class="font-body-md">Peringatan Stok</span>
            </a>
            <a href="/settings" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('settings*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-body-md">Pengaturan</span>
            </a>
        </nav>
    </aside>

    <!-- Sidebar Navigation Desktop (LOGOUT SUDAH DIHAPUS) -->
    <aside class="hidden md:flex flex-col h-screen w-[240px] bg-[#1c1515] border-r border-[#4d3535]/40 p-md z-50 fixed md:sticky top-0 left-0 overflow-y-auto custom-scrollbar">
        <div class="mb-xl px-sm">
            <h1 class="font-display text-headline-sm font-bold text-primary tracking-tight">Logista</h1>
            <p class="font-body-sm text-on-surface-variant opacity-70">Warehouse Control</p>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="/dashboard" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('dashboard') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-body-md">Dashboard</span>
            </a>
            <a href="/items" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('items*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="font-body-md">Data Barang</span>
            </a>
            <a href="/ops" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('ops*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">swap_horiz</span>
                <span class="font-body-md">Transaksi Stok</span>
            </a>
            <a href="/audit-trail" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('audit-trail*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">history</span>
                <span class="font-body-md">Riwayat Aktivitas</span>
            </a>
            <a href="/reports" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('reports*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">analytics</span>
                <span class="font-body-md">Laporan</span>
            </a>
            <a href="/alerts" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('alerts*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">notifications_active</span>
                <span class="font-body-md">Peringatan Stok</span>
            </a>
            <a href="/settings" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('settings*') ? 'bg-primary text-white font-bold' : 'text-on-surface-variant hover:bg-[#2b2020]' }}">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-body-md">Pengaturan</span>
            </a>
        </nav>

        <!-- LOGOUT DI SIDEBAR DESKTOP SUDAH DIHAPUS -->
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-h-screen overflow-hidden w-full relative">
        <!-- Top Navigation -->
        <header class="sticky top-0 z-40 bg-[#1c1515]/80 backdrop-blur-md flex justify-between items-center px-lg py-sm w-full border-b border-[#4d3535]/30 h-[64px]">
            <div class="flex items-center gap-md">
                <!-- HAMBURGER BUTTON untuk mobile -->
                <button id="hamburgerBtn" class="md:hidden text-primary cursor-pointer" onclick="openSidebar()">
                    <span class="material-symbols-outlined">menu</span>
                </button>

                <div class="hidden md:flex items-center gap-2">
                    @php
                        $pageTitle = match(true) {
                            request()->is('dashboard') => ['dashboard', 'Dashboard'],
                            request()->is('items*') => ['inventory_2', 'Master Items'],
                            request()->is('ops*') => ['swap_horiz', 'Inventory Ops'],
                            request()->is('audit-trail*') => ['history', 'Audit Trail'],
                            request()->is('reports*') => ['analytics', 'Reports'],
                            request()->is('alerts*') => ['notifications_active', 'Stock Alerts'],
                            request()->is('settings*') => ['settings', 'Settings'],
                            request()->is('riwayat*') => ['receipt_long', 'Riwayat Pesanan'],
                            default => ['space_dashboard', 'Logista'],
                        };
                    @endphp

                    <span class="material-symbols-outlined text-primary text-[20px]">{{ $pageTitle[0] }}</span>
                    <span class="font-label-bold text-on-surface text-sm tracking-wide">{{ $pageTitle[1] }}</span>
                </div>
            </div>

            <div class="flex items-center gap-md ml-auto">
                <!-- Notification Bell -->
                <div id="stockNotifWrapper" class="relative flex items-center gap-sm">
                    <button
                        type="button"
                        onclick="toggleStockNotif()"
                        class="p-2 text-on-surface hover:bg-surface-container rounded-full transition-colors relative"
                    >
                        <span class="material-symbols-outlined">notifications</span>

                        @if(($jumlahNotifStok ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $jumlahNotifStok }}
                            </span>
                        @endif
                    </button>

                    <div
                        id="stockNotifDropdown"
                        class="hidden absolute right-0 top-12 w-80 max-h-[420px] overflow-y-auto rounded-2xl border border-outline-variant/30 bg-[#1c1515] shadow-2xl z-50"
                    >
                        <div class="p-md border-b border-outline-variant/20">
                            <h4 class="font-label-bold text-on-surface">
                                Notifikasi Stok
                            </h4>
                            <p class="text-xs text-on-surface-variant mt-1">
                                Stok habis dan stok menipis
                            </p>
                        </div>

                        <div class="p-sm space-y-2">
                            @if(($jumlahNotifStok ?? 0) <= 0)
                                <div class="p-md text-center text-on-surface-variant text-sm">
                                    Tidak ada notifikasi stok.
                                </div>
                            @endif

                            @foreach(($stokKritisNotif ?? []) as $item)
                                <a href="/alerts" class="block p-sm rounded-xl border border-error/20 bg-error-container/10 hover:bg-error-container/20 transition-colors no-underline">
                                    <div class="flex items-start gap-sm">
                                        <div class="w-9 h-9 rounded-lg bg-error/20 text-error flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[20px]">error</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-label-bold text-error text-sm">Stok Habis</div>
                                            <div class="text-sm font-bold text-on-surface">{{ $item->nama_barang }}</div>
                                            <div class="text-xs text-on-surface-variant mt-0.5">
                                                SKU: {{ $item->kode_barang }} | Stok: {{ $item->total_stok_hitung ?? 0 }} {{ $item->satuan }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            @foreach(($stokMenipisNotif ?? []) as $item)
                                <a href="/alerts" class="block p-sm rounded-xl border border-tertiary/20 bg-tertiary-container/10 hover:bg-tertiary-container/20 transition-colors no-underline">
                                    <div class="flex items-start gap-sm">
                                        <div class="w-9 h-9 rounded-lg bg-tertiary/20 text-tertiary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[20px]">warning</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-label-bold text-tertiary text-sm">Stok Menipis</div>
                                            <div class="text-sm font-bold text-on-surface">{{ $item->nama_barang }}</div>
                                            <div class="text-xs text-on-surface-variant mt-0.5">
                                                SKU: {{ $item->kode_barang }} | Stok: {{ $item->total_stok_hitung ?? 0 }} {{ $item->satuan }} | Min: {{ $item->stok_minimum }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="p-sm border-t border-outline-variant/20">
                            <a href="/alerts" class="block text-center text-primary text-sm font-label-bold hover:underline">Lihat Semua Peringatan</a>
                        </div>
                    </div>
                </div>

                <!-- PROFILE DROPDOWN -->
                <div class="relative" id="profileWrapper">
                    <button id="profileBtn" class="flex items-center gap-3 glass-panel px-sm py-1.5 rounded-full pr-4 border border-outline-variant/30 cursor-pointer hover:bg-[#2b2020] transition-colors">
                        <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-xs uppercase">
                            {{ auth()->check() ? substr(auth()->user()->name, 0, 2) : 'AD' }}
                        </div>
                        <div class="hidden md:block text-left">
                            <div class="text-[12px] font-bold text-on-surface leading-tight">{{ auth()->user()->name ?? 'Guest' }}</div>
                            <div class="text-[10px] font-medium text-primary uppercase">{{ auth()->user()->role ?? 'Admin' }}</div>
                        </div>
                        <span class="material-symbols-outlined text-sm text-on-surface-variant">expand_more</span>
                    </button>
                    
                    <div id="profileDropdown" class="profile-dropdown">
                        <a href="{{ route('profile.index') ?? '#' }}" class="dropdown-item">
                            <span class="material-symbols-outlined">person</span>
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item dropdown-logout">
                                <span class="material-symbols-outlined">logout</span>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-lg space-y-lg custom-scrollbar">
            @yield('content')
        </div>
    </main>

    <!-- Bottom Nav for Mobile -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-[#1c1515]/90 backdrop-blur-lg flex justify-around items-center py-sm border-t border-outline-variant/20 z-50">
        <a href="/dashboard" class="flex flex-col items-center gap-xs {{ request()->is('dashboard') ? 'text-primary' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" {!! request()->is('dashboard') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>dashboard</span>
            <span class="text-[10px] font-label-bold">Home</span>
        </a>
        <a href="/ops" class="flex flex-col items-center gap-xs {{ request()->is('ops*') ? 'text-primary' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" {!! request()->is('ops*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>swap_horiz</span>
            <span class="text-[10px] font-label-bold">Ops</span>
        </a>
        <a href="/items" class="flex flex-col items-center gap-xs {{ request()->is('items*') ? 'text-primary' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" {!! request()->is('items*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>inventory_2</span>
            <span class="text-[10px] font-label-bold">Items</span>
        </a>
        <a href="/audit-trail" class="flex flex-col items-center gap-xs {{ request()->is('audit-trail*') ? 'text-primary' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" {!! request()->is('audit-trail*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>history</span>
            <span class="text-[10px] font-label-bold">Audit</span>
        </a>
        <a href="/alerts" class="flex flex-col items-center gap-xs {{ request()->is('alerts*') ? 'text-primary' : 'text-on-surface-variant' }}">
            <span class="material-symbols-outlined" {!! request()->is('alerts*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>notifications_active</span>
            <span class="text-[10px] font-label-bold">Alerts</span>
        </a>
    </nav>

    <script>
        // ========================================
        // NOTIFIKASI STOK
        // ========================================
        function toggleStockNotif() {
            const dropdown = document.getElementById('stockNotifDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function(event) {
            const wrapper = document.getElementById('stockNotifWrapper');
            const dropdown = document.getElementById('stockNotifDropdown');
            if (!wrapper || !dropdown) return;
            if (!wrapper.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // ========================================
        // MOBILE SIDEBAR & OVERLAY & SCROLL LOCK
        // ========================================
        function openSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                document.body.classList.add('no-scroll');
            }
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }
        }
        
        // ========================================
        // PROFIL DROPDOWN
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            const profileWrapper = document.getElementById('profileWrapper');
            
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (profileWrapper && !profileWrapper.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                });
            }
            
            // Tutup sidebar saat klik link di dalamnya (mobile)
            const mobileSidebar = document.getElementById('mobileSidebar');
            if (mobileSidebar) {
                mobileSidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 768) {
                            closeSidebar();
                        }
                    });
                });
            }
            
            // Tutup sidebar saat resize ke desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    closeSidebar();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>