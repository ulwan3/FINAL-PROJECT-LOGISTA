@extends('layouts.app')

@section('header_action')
<button class="bg-primary hover:bg-primary/90 text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm transition-all active:scale-95">
    <span class="material-symbols-outlined">add</span> Add Stock
</button>
@endsection

@section('content')
<div class="flex justify-between items-end">
    <div>
        <h2 class="font-headline-lg text-on-surface">Peringatan Stok</h2>
        <p class="text-on-surface-variant font-body-md mt-1">Sistem mendeteksi {{ $kritis->count() + $menipis->count() }} item yang membutuhkan pengisian ulang segera.</p>
    </div>
</div>

<!-- Dashboard Stats Bento Grid - 2 cards seimbang -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-md mt-lg">
    <div class="glass-card p-lg rounded-xl flex flex-col justify-between h-32 border-l-4 border-error">
        <span class="text-on-surface-variant font-label-bold">STATUS KRITIS</span>
        <div class="flex items-baseline gap-sm">
            <span class="font-display text-[40px] text-error font-bold leading-none">{{ str_pad($kritis->count(), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="text-on-surface-variant font-body-sm">Barang</span>
        </div>
    </div>
    <div class="glass-card p-lg rounded-xl flex flex-col justify-between h-32 border-l-4 border-tertiary">
        <span class="text-on-surface-variant font-label-bold">STATUS MENIPIS</span>
        <div class="flex items-baseline gap-sm">
            <span class="font-display text-[40px] text-tertiary font-bold leading-none">{{ str_pad($menipis->count(), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="text-on-surface-variant font-body-sm">Barang</span>
        </div>
    </div>
</div>

<!-- Alerts List Section -->
<div class="space-y-md mt-lg">
    <div class="grid grid-cols-12 gap-md px-lg text-on-surface-variant font-label-bold uppercase tracking-wider">
        <div class="col-span-5">Informasi Produk</div>
        <div class="col-span-2 text-center">Stok Saat Ini</div>
        <div class="col-span-2 text-center">Ambang Batas</div>
        <div class="col-span-3 text-right">Aksi</div>
    </div>

    <!-- Critical Items -->
    @foreach($kritis as $item)
    <div class="glass-card rounded-xl p-md group hover:bg-error-container/5 transition-all duration-300 border border-error/20">
        <div class="grid grid-cols-12 gap-md items-center">
            <div class="col-span-5 flex items-center gap-md">
                <div class="w-16 h-16 rounded-lg bg-surface-container overflow-hidden border border-outline-variant/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-outline">inventory_2</span>
                </div>
                <div>
                    <h4 class="font-headline-sm text-body-md font-bold text-on-surface">{{ $item->nama_barang }}</h4>
                    <div class="flex gap-sm mt-xs">
                        <span class="font-label-bold px-sm py-[2px] bg-error-container/20 text-error rounded-full text-[10px]">KOSONG</span>
                        <span class="font-label-muted text-on-surface-variant text-[12px]">SKU: {{ $item->kode_barang }}</span>
                    </div>
                </div>
            </div>
            <div class="col-span-2 text-center">
                <div class="flex flex-col">
                    <span class="text-headline-sm font-bold text-error">0 {{ $item->satuan }}</span>
                </div>
            </div>
            <div class="col-span-2 text-center">
                <span class="text-body-md font-medium text-on-surface-variant">Min. {{ $item->stok_minimum }} {{ $item->satuan }}</span>
            </div>
            <div class="col-span-3 flex justify-end gap-sm">
                <!-- TOMBOL RESTOCK DIUBAH MENJADI LINK KE DASHBOARD -->
                <a href="{{ route('dashboard') }}?restock_id={{ $item->id }}&restock_name={{ $item->nama_barang }}&restock_min={{ $item->stok_minimum }}&restock_satuan={{ $item->satuan }}" 
                   class="bg-error text-on-error px-md py-2 rounded-lg font-label-bold transition-all hover:scale-105 active:scale-95 flex items-center gap-xs shadow-[0_0_20px_rgba(255,180,171,0.3)] no-underline">
                    <span class="material-symbols-outlined text-[18px]">shopping_cart_checkout</span> Restock
                </a>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Warning Items -->
    @foreach($menipis as $item)
    <div class="glass-card rounded-xl p-md group hover:bg-tertiary-container/5 transition-all duration-300 border border-tertiary/20">
        <div class="grid grid-cols-12 gap-md items-center">
            <div class="col-span-5 flex items-center gap-md">
                <div class="w-16 h-16 rounded-lg bg-surface-container overflow-hidden border border-outline-variant/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-outline">inventory_2</span>
                </div>
                <div>
                    <h4 class="font-headline-sm text-body-md font-bold text-on-surface">{{ $item->nama_barang }}</h4>
                    <div class="flex gap-sm mt-xs">
                        <span class="font-label-bold px-sm py-[2px] bg-tertiary-container/20 text-tertiary rounded-full text-[10px]">MENIPIS</span>
                        <span class="font-label-muted text-on-surface-variant text-[12px]">SKU: {{ $item->kode_barang }}</span>
                    </div>
                </div>
            </div>
            <div class="col-span-2 text-center">
                <div class="flex flex-col">
                    <span class="text-headline-sm font-bold text-tertiary">{{ $item->stok }} {{ $item->satuan }}</span>
                </div>
            </div>
            <div class="col-span-2 text-center">
                <span class="text-body-md font-medium text-on-surface-variant">Min. {{ $item->stok_minimum }} {{ $item->satuan }}</span>
            </div>
            <div class="col-span-3 flex justify-end gap-sm">
                <!-- TOMBOL RESTOCK DIUBAH MENJADI LINK KE DASHBOARD -->
                <a href="{{ route('dashboard') }}?restock_id={{ $item->id }}&restock_name={{ $item->nama_barang }}&restock_min={{ $item->stok_minimum }}&restock_satuan={{ $item->satuan }}" 
                   class="bg-tertiary text-on-tertiary px-md py-2 rounded-lg font-label-bold transition-all hover:scale-105 active:scale-95 flex items-center gap-xs shadow-[0_0_15px_rgba(231,195,101,0.2)] no-underline">
                    <span class="material-symbols-outlined text-[18px]">shopping_cart_checkout</span> Restock
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection