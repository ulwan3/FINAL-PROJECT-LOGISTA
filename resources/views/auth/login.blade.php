@extends('layouts.auth')

@section('content')
<div class="z-10 w-full max-w-md p-md">
    <div class="glass-panel rounded-2xl p-xl shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-tertiary to-primary"></div>
        
        <div class="text-center mb-xl">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-container text-on-primary-container mb-md shadow-lg ring-4 ring-primary/20">
                <span class="material-symbols-outlined text-[32px]">inventory_2</span>
            </div>
            <h1 class="font-display text-headline-md text-on-surface font-bold tracking-tight">Logista</h1>
            <p class="font-body-md text-on-surface-variant mt-sm">Sistem Keamanan Inventori & Logistik</p>
        </div>

        <form method="POST" action="/login" class="space-y-md">
            @csrf
            
            @if($errors->any())
                <div class="p-3 bg-error-container/50 text-error rounded-xl text-sm border border-error/30 font-label-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-y-sm">
                <label for="email" class="font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px] ml-1">Alamat Email</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">mail</span>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full bg-surface-container-highest border border-outline-variant/30 text-on-surface font-body-md rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all placeholder:text-on-surface-variant/50" placeholder="admin@logista.com" required>
                </div>
            </div>

            <div class="space-y-sm">
                <div class="flex justify-between items-center ml-1">
                    <label for="password" class="font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Sandi Akses</label>
                    <a href="#" class="font-label-bold text-primary hover:text-primary-fixed transition-colors text-[11px]">Lupa sandi?</a>
                </div>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </span>
                    <input type="password" id="password" name="password" class="w-full bg-surface-container-highest border border-outline-variant/30 text-on-surface font-body-md rounded-xl pl-12 pr-12 py-3 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all placeholder:text-on-surface-variant/50" placeholder="••••••••" required>
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none cursor-pointer active:scale-95 transition-transform">
                        <span id="togglePasswordIcon" class="material-symbols-outlined text-[20px]">visibility_off</span>
                    </button>
                </div>
            </div>

            <div class="pt-sm">
                <button type="submit" class="w-full bg-primary text-on-primary font-label-bold text-body-md py-3 rounded-xl hover:brightness-110 active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(207,188,255,0.3)] flex justify-center items-center gap-sm cursor-pointer">
                    <span>Masuk ke Sistem</span>
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
            </div>
        </form>

        <div class="mt-xl text-center border-t border-outline-variant/20 pt-md">
            <p class="font-label-muted text-on-surface-variant text-[11px]">
                Diakses pada jaringan privat aman.<br>Logista v2.4.1 © 2023
            </p>
        </div>
    </div>
</div>

<script>
    // Fitur Show/Hide Password dengan TEKAN DAN TAHAN
    (function() {
        var toggleBtn = document.getElementById('togglePassword');
        var passwordInput = document.getElementById('password');
        var iconSpan = document.getElementById('togglePasswordIcon');
        
        if (toggleBtn && passwordInput && iconSpan) {
            
            // Fungsi untuk menampilkan password (mata terbuka)
            function showPassword() {
                passwordInput.type = 'text';
                iconSpan.textContent = 'visibility';
            }
            
            // Fungsi untuk menyembunyikan password (mata tertutup)
            function hidePassword() {
                passwordInput.type = 'password';
                iconSpan.textContent = 'visibility_off';
            }
            
            // Event: saat tombol ditekan (mouse down)
            toggleBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                showPassword();
            });
            
            // Event: saat tombol dilepas (mouse up)
            toggleBtn.addEventListener('mouseup', function(e) {
                e.preventDefault();
                hidePassword();
            });
            
            // Event: jika kursor keluar dari tombol saat masih ditekan
            toggleBtn.addEventListener('mouseleave', function(e) {
                hidePassword();
            });
            
            // Dukungan untuk touch screen (ponsel)
            toggleBtn.addEventListener('touchstart', function(e) {
                e.preventDefault();
                showPassword();
            });
            
            toggleBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                hidePassword();
            });
            
            toggleBtn.addEventListener('touchcancel', function(e) {
                e.preventDefault();
                hidePassword();
            });
        }
    })();
</script>
@endsection