@extends('layouts.app')

@section('header_action')
<button class="bg-primary text-on-primary font-label-bold px-lg py-2 rounded-lg hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(207,188,255,0.2)]">
    <span class="material-symbols-outlined text-[20px]">print</span>
    Cetak Laporan
</button>
@endsection

@section('content')
<section class="flex flex-col gap-sm mb-md">
    <h2 class="font-headline-md text-on-surface">Laporan & Analitik (Reports)</h2>
    <p class="font-body-sm text-on-surface-variant">Analisis pergerakan barang, utilisasi gudang, dan nilai inventori.</p>
</section>

<!-- Tab Navigation -->
<div class="flex gap-md border-b border-outline-variant/30 mb-lg overflow-x-auto custom-scrollbar pb-1">
    <button class="pb-2 border-b-2 border-primary text-primary font-label-bold px-2 whitespace-nowrap">Ringkasan Eksekutif</button>
    <button class="pb-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-label-bold px-2 whitespace-nowrap transition-colors">Pergerakan Stok (In/Out)</button>
    <button class="pb-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-label-bold px-2 whitespace-nowrap transition-colors">Nilai Inventori</button>
    <button class="pb-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-label-bold px-2 whitespace-nowrap transition-colors">Kinerja Supplier</button>
</div>

<!-- Report Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-md mb-lg">
    <div class="glass-panel p-lg rounded-xl border-t-2 border-primary">
        <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Total Barang Masuk (Bulan Ini)</h4>
        <div class="flex items-baseline gap-2">
            <span class="font-display text-[32px] text-on-surface leading-none">{{ number_format($masuk) }}</span>
            <span class="text-body-sm text-on-surface-variant">Unit</span>
        </div>
        <p class="text-[12px] text-green-400 mt-2 flex items-center gap-1 font-bold">
            <span class="material-symbols-outlined text-[14px]">trending_up</span> +15% dari bulan lalu
        </p>
    </div>

    <div class="glass-panel p-lg rounded-xl border-t-2 border-secondary">
        <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Total Barang Keluar (Bulan Ini)</h4>
        <div class="flex items-baseline gap-2">
            <span class="font-display text-[32px] text-on-surface leading-none">{{ number_format($keluar) }}</span>
            <span class="text-body-sm text-on-surface-variant">Unit</span>
        </div>
        <p class="text-[12px] text-error mt-2 flex items-center gap-1 font-bold">
            <span class="material-symbols-outlined text-[14px]">trending_down</span> -5% dari bulan lalu
        </p>
    </div>

    <div class="glass-panel p-lg rounded-xl border-t-2 border-tertiary">
        <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Rata-rata Lama Stok (Turnover)</h4>
        <div class="flex items-baseline gap-2">
            <span class="font-display text-[32px] text-on-surface leading-none">14</span>
            <span class="text-body-sm text-on-surface-variant">Hari</span>
        </div>
        <p class="text-[12px] text-on-surface-variant mt-2 font-medium">
            Lebih cepat 2 hari dari target
        </p>
    </div>
</div>

<!-- Main Chart Area -->
<div class="glass-panel p-lg rounded-xl flex flex-col mb-lg h-[400px]">
    <div class="flex justify-between items-center mb-md">
        <h3 class="font-headline-sm text-on-surface">Grafik Barang Masuk vs Keluar (30 Hari Terakhir)</h3>
    </div>
    <div class="flex-1 w-full relative border-b border-l border-outline-variant/30 mt-4 flex items-end gap-4 p-4 pl-8">
        
        <!-- Y-Axis Labels -->
        <div class="absolute left-[-30px] top-0 bottom-0 flex flex-col justify-between text-[10px] text-on-surface-variant py-4">
            <span>1K</span>
            <span>750</span>
            <span>500</span>
            <span>250</span>
            <span>0</span>
        </div>

        <!-- Grid Lines -->
        <div class="absolute left-0 right-0 top-[25%] h-px bg-outline-variant/10"></div>
        <div class="absolute left-0 right-0 top-[50%] h-px bg-outline-variant/10"></div>
        <div class="absolute left-0 right-0 top-[75%] h-px bg-outline-variant/10"></div>

        <!-- Mock Bars Data -->
        <div class="flex-1 flex justify-center items-end gap-1 group relative">
            <div class="w-full max-w-[20px] bg-primary/80 h-[60%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <div class="w-full max-w-[20px] bg-secondary/80 h-[40%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <span class="absolute -bottom-6 text-[10px] text-on-surface-variant">Minggu 1</span>
        </div>
        <div class="flex-1 flex justify-center items-end gap-1 relative">
            <div class="w-full max-w-[20px] bg-primary/80 h-[80%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <div class="w-full max-w-[20px] bg-secondary/80 h-[35%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <span class="absolute -bottom-6 text-[10px] text-on-surface-variant">Minggu 2</span>
        </div>
        <div class="flex-1 flex justify-center items-end gap-1 relative">
            <div class="w-full max-w-[20px] bg-primary/80 h-[45%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <div class="w-full max-w-[20px] bg-secondary/80 h-[90%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <span class="absolute -bottom-6 text-[10px] text-on-surface-variant">Minggu 3</span>
        </div>
        <div class="flex-1 flex justify-center items-end gap-1 relative">
            <div class="w-full max-w-[20px] bg-primary/80 h-[70%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <div class="w-full max-w-[20px] bg-secondary/80 h-[65%] rounded-t-sm hover:brightness-125 transition-all cursor-pointer"></div>
            <span class="absolute -bottom-6 text-[10px] text-on-surface-variant">Minggu 4</span>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex justify-center gap-lg mt-8">
        <div class="flex items-center gap-xs">
            <div class="w-3 h-3 bg-primary rounded-sm"></div>
            <span class="text-[12px] text-on-surface-variant">Barang Masuk</span>
        </div>
        <div class="flex items-center gap-xs">
            <div class="w-3 h-3 bg-secondary rounded-sm"></div>
            <span class="text-[12px] text-on-surface-variant">Barang Keluar</span>
        </div>
    </div>
</div>

<!-- Detail Data Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div class="glass-panel p-md rounded-xl">
        <h3 class="font-headline-sm text-on-surface mb-md">Top 5 Barang Paling Sering Keluar</h3>
        <ul class="space-y-sm">
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <span class="text-on-surface font-body-sm">Minyak Goreng Bimoli 2L</span>
                <span class="text-secondary font-bold font-mono">1,450 Dus</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <span class="text-on-surface font-body-sm">Beras Premium 5kg</span>
                <span class="text-secondary font-bold font-mono">980 Sak</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <span class="text-on-surface font-body-sm">Gula Pasir Gulaku 1kg</span>
                <span class="text-secondary font-bold font-mono">850 Kg</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <span class="text-on-surface font-body-sm">Tepung Terigu 1kg</span>
                <span class="text-secondary font-bold font-mono">600 Pcs</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <span class="text-on-surface font-body-sm">Indomie Goreng</span>
                <span class="text-secondary font-bold font-mono">420 Dus</span>
            </li>
        </ul>
    </div>
    
    <div class="glass-panel p-md rounded-xl">
        <h3 class="font-headline-sm text-on-surface mb-md">Top 3 Supplier Bulan Ini</h3>
        <ul class="space-y-sm">
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <div>
                    <div class="text-on-surface font-body-sm">PT. Salim Ivomas</div>
                    <div class="text-on-surface-variant text-[10px]">98% Ketepatan Waktu</div>
                </div>
                <span class="text-primary font-bold font-mono">Rp 450M</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <div>
                    <div class="text-on-surface font-body-sm">CV. Pangan Sejahtera</div>
                    <div class="text-on-surface-variant text-[10px]">95% Ketepatan Waktu</div>
                </div>
                <span class="text-primary font-bold font-mono">Rp 320M</span>
            </li>
            <li class="flex justify-between items-center p-2 hover:bg-surface-container/50 rounded-lg">
                <div>
                    <div class="text-on-surface font-body-sm">Grosir Tani Jaya</div>
                    <div class="text-on-surface-variant text-[10px]">92% Ketepatan Waktu</div>
                </div>
                <span class="text-primary font-bold font-mono">Rp 150M</span>
            </li>
        </ul>
    </div>
</div>
@endsection
