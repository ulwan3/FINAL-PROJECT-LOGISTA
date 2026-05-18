@extends('layouts.app')

@section('header_action')
<button class="bg-surface-container-highest text-on-surface font-label-bold px-lg py-2 rounded-lg hover:bg-surface-bright border border-outline-variant/30 transition-all flex items-center gap-2">
    <span class="material-symbols-outlined text-[20px]">download</span>
    Unduh Log (.csv)
</button>
@endsection

@section('content')
<section class="flex flex-col gap-sm">
    <h2 class="font-headline-md text-on-surface">Audit Trail (Riwayat Sistem)</h2>
    <p class="font-body-sm text-on-surface-variant">Log aktivitas operasional dan keamanan dari seluruh pengguna sistem.</p>
</section>

<!-- Filter Section -->
<form action="{{ route('audit.trail') }}" method="GET" class="glass-panel p-md rounded-xl flex flex-col md:flex-row gap-md items-end">
    
    <div class="flex-1 w-full flex flex-col gap-xs">
        <label class="font-label-bold text-on-surface-variant text-[11px] uppercase tracking-wider ml-1">Rentang Waktu</label>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full bg-surface-container-low border border-outline-variant/30 text-on-surface font-body-sm rounded-lg px-3 py-2 outline-none focus:border-primary">
    </div>
    
    <div class="flex-1 w-full flex flex-col gap-xs">
        <label class="font-label-bold text-on-surface-variant text-[11px] uppercase tracking-wider ml-1">Modul / Jenis</label>
        <select name="modul" class="w-full bg-surface-container-low border border-outline-variant/30 text-on-surface font-body-sm rounded-lg px-3 py-2 outline-none focus:border-primary">
            <option value="Semua Aktivitas">Semua Aktivitas</option>
            <option value="masuk" {{ request('modul') == 'masuk' ? 'selected' : '' }}>Stok Masuk</option>
            <option value="keluar" {{ request('modul') == 'keluar' ? 'selected' : '' }}>Stok Keluar</option>
        </select>
    </div>
    
    <div class="flex-1 w-full flex flex-col gap-xs">
        <label class="font-label-bold text-on-surface-variant text-[11px] uppercase tracking-wider ml-1">Operator</label>
        <select name="operator" class="w-full bg-surface-container-low border border-outline-variant/30 text-on-surface font-body-sm rounded-lg px-3 py-2 outline-none focus:border-primary">
            <option value="Semua Operator">Semua Operator</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('operator') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <button type="submit" class="w-full md:w-auto px-lg py-2 bg-primary text-on-primary font-label-bold rounded-lg hover:brightness-110 active:scale-95 transition-all">
        Terapkan
    </button>
</form>

<!-- Audit Timeline / Table -->
<section class="glass-panel rounded-xl overflow-hidden flex flex-col mt-lg">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-surface-container-high border-b border-outline-variant/30">
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">WAKTU (WIB)</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">OPERATOR</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">AKTIVITAS</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">DETAIL TINDAKAN</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant text-right">IP ADDRESS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                @forelse($transaksis as $trx)
                <tr class="hover:bg-surface-container/50 transition-colors">
                    <td class="px-lg py-md">
                        <div class="font-body-md text-on-surface">{{ $trx->created_at->format('d M Y') }}</div>
                        <div class="font-label-muted text-on-surface-variant mt-0.5">{{ $trx->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="px-lg py-md">
                        <div class="flex items-center gap-sm">
                            <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xs">{{ substr($trx->user->name ?? 'SYS', 0, 2) }}</div>
                            <div>
                                <div class="font-body-md text-on-surface">{{ $trx->user->name ?? 'System' }}</div>
                                <div class="font-label-muted text-on-surface-variant mt-0.5">Role: {{ $trx->user->role ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-lg py-md">
                        @if($trx->jenis == 'masuk')
                            <span class="px-2 py-1 rounded bg-green-500/10 text-green-400 font-label-bold text-[11px] border border-green-500/20 flex items-center gap-1 w-max">
                                <span class="material-symbols-outlined text-[14px]">add_circle</span> Stok Masuk
                            </span>
                        @else
                            <span class="px-2 py-1 rounded bg-error/10 text-error font-label-bold text-[11px] border border-error/20 flex items-center gap-1 w-max">
                                <span class="material-symbols-outlined text-[14px]">remove_circle</span> Stok Keluar
                            </span>
                        @endif
                    </td>
                    <td class="px-lg py-md">
                        <div class="font-body-sm text-on-surface-variant">Transaksi <strong class="text-on-surface">{{ $trx->kode_transaksi }}</strong>. Barang <strong class="text-primary">{{ $trx->barang->nama_barang ?? '-' }}</strong> sebanyak {{ $trx->jumlah }}. {{ $trx->keterangan }}</div>
                    </td>
                    <td class="px-lg py-md text-right font-mono text-[12px] text-on-surface-variant">N/A</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-lg py-md text-center text-on-surface-variant">Belum ada riwayat transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="p-md border-t border-outline-variant/20 bg-surface-container-low/50">
        {{ $transaksis->links('pagination::tailwind') }}
    </div>
</section>
@endsection
