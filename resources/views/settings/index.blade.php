@extends('layouts.app')

@section('header_action')
<button class="bg-primary text-on-primary font-label-bold px-lg py-2 rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(207,188,255,0.2)]">
    Simpan Perubahan
</button>
@endsection

@section('content')
<section class="flex flex-col gap-sm mb-md">
    <h2 class="font-headline-md text-on-surface">Pengaturan Sistem</h2>
    <p class="font-body-sm text-on-surface-variant">Konfigurasi profil pengguna, preferensi aplikasi, dan manajemen hak akses.</p>
</section>

<div class="grid grid-cols-1 md:grid-cols-4 gap-lg">
    <!-- Settings Sidebar -->
    <div class="md:col-span-1 flex flex-col gap-sm">
        <button class="flex items-center gap-md px-md py-3 rounded-xl bg-primary-container text-on-primary-container font-bold transition-all w-full text-left">
            <span class="material-symbols-outlined">person</span>
            Profil Saya
        </button>
        <button class="flex items-center gap-md px-md py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all w-full text-left border border-transparent">
            <span class="material-symbols-outlined">tune</span>
            Preferensi Sistem
        </button>
        <button class="flex items-center gap-md px-md py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all w-full text-left border border-transparent">
            <span class="material-symbols-outlined">notifications</span>
            Pemberitahuan
        </button>
        <button class="flex items-center gap-md px-md py-3 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all w-full text-left border border-transparent">
            <span class="material-symbols-outlined">security</span>
            Keamanan & Akses
        </button>
    </div>

    <!-- Settings Content Area -->
    <div class="md:col-span-3 glass-panel p-lg rounded-2xl flex flex-col gap-lg min-h-[500px]">
        
        <!-- Profile Section -->
        <div>
            <h3 class="font-headline-sm text-on-surface mb-lg pb-3 border-b border-outline-variant/30">Informasi Pribadi</h3>
            <div class="flex flex-col lg:flex-row items-start gap-xl">
                <!-- Avatar Upload -->
                <div class="flex flex-col items-center gap-sm shrink-0">
                    <div class="w-32 h-32 rounded-full bg-surface-container-highest border-2 border-primary/50 flex items-center justify-center text-primary relative group cursor-pointer overflow-hidden shadow-lg">
                        <span class="material-symbols-outlined text-[50px]">account_circle</span>
                        <div class="absolute inset-0 bg-surface/60 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="material-symbols-outlined text-on-surface">photo_camera</span>
                        </div>
                    </div>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider font-bold mt-2 hover:text-primary cursor-pointer transition-colors">Ubah Foto</span>
                </div>

                <!-- Profile Form -->
                <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-lg">
                    <div class="flex flex-col gap-2">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Nama Lengkap</label>
                        <input type="text" class="w-full bg-surface-container border border-outline-variant/30 rounded-xl py-3 px-4 text-on-surface font-body-md focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all" value="{{ $user->name ?? 'Admin Logista' }}">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Username ID</label>
                        <input type="text" class="w-full bg-surface-container border border-outline-variant/30 rounded-xl py-3 px-4 text-on-surface font-body-md focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all" value="{{ $user->username ?? 'admin_01' }}">
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Alamat Email</label>
                        <input type="email" class="w-full bg-surface-container border border-outline-variant/30 rounded-xl py-3 px-4 text-on-surface font-body-md focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all" value="{{ $user->email ?? 'admin@logista.com' }}">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Peran (Role)</label>
                        <input type="text" class="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-xl py-3 px-4 text-primary font-bold font-body-md cursor-not-allowed uppercase opacity-80" value="{{ $user->role ?? 'ADMIN' }}" readonly>
                        <span class="text-[11px] text-on-surface-variant mt-1 italic">Hanya Super Admin yang dapat mengubah role.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security / Password Section -->
        <div class="mt-xl pt-lg border-t border-outline-variant/20">
            <h3 class="font-headline-sm text-on-surface mb-lg pb-3 border-b border-outline-variant/30">Ubah Kata Sandi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div class="flex flex-col gap-2">
                    <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Sandi Lama</label>
                    <input type="password" class="w-full bg-surface-container border border-outline-variant/30 rounded-xl py-3 px-4 text-on-surface font-body-md focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all" placeholder="••••••••">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-bold text-on-surface-variant uppercase tracking-wider text-xs">Sandi Baru</label>
                    <input type="password" class="w-full bg-surface-container border border-outline-variant/30 rounded-xl py-3 px-4 text-on-surface font-body-md focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all" placeholder="••••••••">
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection
