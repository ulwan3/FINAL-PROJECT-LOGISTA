<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Logista - Warehouse Control Dashboard')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0F1117] text-on-surface flex min-h-screen">
    <!-- Sidebar Navigation -->
    <aside class="hidden md:flex flex-col h-screen w-[240px] bg-surface-container-low border-r border-outline-variant/30 p-md z-50 fixed md:sticky top-0 left-0 overflow-y-auto custom-scrollbar">
        <div class="mb-xl px-sm">
            <h1 class="font-display text-headline-sm font-bold text-primary tracking-tight">Logista</h1>
            <p class="font-body-sm text-on-surface-variant opacity-70">Warehouse Control</p>
        </div>
        
        <nav class="flex-1 space-y-1">
            <a href="/dashboard" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('dashboard') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-body-md">Dashboard</span>
            </a>
            <a href="/items" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('items*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="font-body-md">Master Items</span>
            </a>
            <a href="/ops" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('ops*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">swap_horiz</span>
                <span class="font-body-md">Inventory Ops</span>
            </a>
            <a href="/audit-trail" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('audit-trail*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">history</span>
                <span class="font-body-md">Audit Trail</span>
            </a>
            <a href="/reports" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('reports*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">analytics</span>
                <span class="font-body-md">Reports</span>
            </a>
            <a href="/alerts" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('alerts*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">notifications_active</span>
                <span class="font-body-md">Stock Alerts</span>
            </a>
            <a href="/settings" class="flex items-center gap-md px-md py-sm rounded-xl transition-all duration-200 {{ request()->is('settings*') ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-body-md">Settings</span>
            </a>
        </nav>
        
        <div class="mt-auto px-lg mb-lg">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-md px-md py-sm rounded-xl text-error hover:bg-error-container/20 w-full transition-all duration-200">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-body-md font-bold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-h-screen overflow-hidden w-full relative">
        <!-- Top Navigation -->
        <header class="sticky top-0 z-40 bg-surface/70 backdrop-blur-md flex justify-between items-center px-lg py-sm w-full border-b border-outline-variant/20 h-[64px]">
            <div class="flex items-center gap-md">
                <span class="md:hidden material-symbols-outlined text-primary cursor-pointer">menu</span>
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
                <div class="flex items-center gap-sm">
                    <button class="p-2 text-on-surface hover:bg-surface-container rounded-full transition-colors relative">
                        <span class="material-symbols-outlined">notifications</span>
                        @if(request()->is('alerts*'))
                            <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
                        @endif
                    </button>
                </div>
                
                <div class="flex items-center gap-3 glass-panel px-sm py-1.5 rounded-full pr-4 border border-outline-variant/30">
                    <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-xs uppercase">
                        {{ auth()->check() ? substr(auth()->user()->name, 0, 2) : 'AD' }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-[12px] font-bold text-on-surface leading-tight">{{ auth()->user()->name ?? 'Guest' }}</div>
                        <div class="text-[10px] font-medium text-primary uppercase">{{ auth()->user()->role ?? 'Admin' }}</div>
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
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-surface-container-high/90 backdrop-blur-lg flex justify-around items-center py-sm border-t border-outline-variant/20 z-50">
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

    @stack('scripts')
</body>
</html>
