@extends('layouts.app')

@section('title', 'Logista - Inventory Operations')

@section('content')
<section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-lg">
    <div>
        <h2 class="font-display text-headline-md font-bold text-on-surface">Inventory Operations</h2>
        <p class="font-body-md text-on-surface-variant mt-1">Monitoring real-time transaksi barang masuk, keluar, dan mutasi dari operator.</p>
    </div>
    <div class="flex items-center gap-sm px-md py-sm rounded-xl border border-outline-variant/30 bg-surface-container text-on-surface-variant text-sm">
        <span class="material-symbols-outlined text-[16px] text-primary">info</span>
        <span class="font-label-bold">Mode: Monitoring Only</span>
    </div>
</section>

{{-- Stats Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-lg mb-xl">
    {{-- Barang Masuk --}}
    <div class="glass-panel p-lg rounded-2xl border border-green-500/20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-28 h-28 bg-green-500/10 rounded-full blur-3xl -mr-8 -mt-8 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-sm">
            <span class="text-xs font-label-bold text-on-surface-variant uppercase tracking-wider">Barang Masuk</span>
            <span class="material-symbols-outlined text-green-400 text-[20px]">south_east</span>
        </div>
        <p class="text-3xl font-bold text-green-400">+{{ $totalMasuk }}</p>
        <p class="text-xs text-on-surface-variant mt-1">{{ $countMasuk }} transaksi hari ini</p>
    </div>

    {{-- Barang Keluar --}}
    <div class="glass-panel p-lg rounded-2xl border border-error/20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-28 h-28 bg-error/10 rounded-full blur-3xl -mr-8 -mt-8 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-sm">
            <span class="text-xs font-label-bold text-on-surface-variant uppercase tracking-wider">Barang Keluar</span>
            <span class="material-symbols-outlined text-error text-[20px]">north_east</span>
        </div>
        <p class="text-3xl font-bold text-error">-{{ $totalKeluar }}</p>
        <p class="text-xs text-on-surface-variant mt-1">{{ $countKeluar }} transaksi hari ini</p>
    </div>

    {{-- Mutasi --}}
    <div class="glass-panel p-lg rounded-2xl border border-tertiary/20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-28 h-28 bg-tertiary/10 rounded-full blur-3xl -mr-8 -mt-8 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-sm">
            <span class="text-xs font-label-bold text-on-surface-variant uppercase tracking-wider">Mutasi Stok</span>
            <span class="material-symbols-outlined text-[20px]" style="color: var(--md-sys-color-tertiary, #cfbcff)">sync_alt</span>
        </div>
        <p class="text-3xl font-bold" style="color: var(--md-sys-color-tertiary, #cfbcff)">{{ $countMutasi }}</p>
        <p class="text-xs text-on-surface-variant mt-1">{{ $countMutasi }} transaksi hari ini</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex border-b border-outline-variant/30 mb-lg">
    <button onclick="switchTab('masuk')" id="tab-masuk" class="px-lg py-sm font-label-bold text-primary border-b-2 border-primary transition-colors flex items-center gap-sm">
        <span class="material-symbols-outlined text-[18px]">south_east</span> Barang Masuk
    </button>
    <button onclick="switchTab('keluar')" id="tab-keluar" class="px-lg py-sm font-label-bold text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center gap-sm">
        <span class="material-symbols-outlined text-[18px]">north_east</span> Barang Keluar
    </button>
    <button onclick="switchTab('mutasi')" id="tab-mutasi" class="px-lg py-sm font-label-bold text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center gap-sm">
        <span class="material-symbols-outlined text-[18px]">sync_alt</span> Mutasi Stok
    </button>
</div>

{{-- TAB: BARANG MASUK --}}
<div id="panel-masuk" class="glass-panel rounded-2xl border border-outline-variant/30 overflow-hidden animate-fade-in-up">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-sm p-lg border-b border-outline-variant/20">
        <div>
            <h3 class="font-headline-sm text-on-surface">Riwayat Barang Masuk</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Data yang diinput oleh operator melalui aplikasi mobile</p>
        </div>
        <a href="/audit-trail" class="text-primary text-sm font-label-bold hover:underline flex items-center gap-xs">
            <span class="material-symbols-outlined text-[16px]">open_in_new</span> Lihat Audit Trail
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/30 bg-surface-container/40">
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Waktu</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Kode Transaksi</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Barang</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px] text-right">Jumlah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sebelum</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sesudah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Operator</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiMasuk as $trx)
                <tr class="border-b border-outline-variant/10 hover:bg-surface-container/50 transition-colors">
                    <td class="py-3 px-4 text-sm text-on-surface-variant whitespace-nowrap">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-3 px-4 text-xs font-mono text-on-surface-variant">{{ $trx->kode_transaksi }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface">
                        {{ $trx->barang->nama_barang ?? '-' }}
                        <span class="text-xs text-on-surface-variant font-normal block">{{ $trx->barang->kode_barang ?? '' }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm font-mono font-bold text-green-400 text-right">+{{ $trx->jumlah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant text-center">{{ $trx->stok_sebelum }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface text-center">{{ $trx->stok_sesudah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant">{{ $trx->user->name ?? 'Operator' }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant max-w-[180px] truncate" title="{{ $trx->keterangan }}">{{ $trx->keterangan ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-xl text-center text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined text-4xl block mb-sm opacity-30">inbox</span>
                        Belum ada transaksi barang masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksiMasuk->hasPages())
    <div class="p-md border-t border-outline-variant/20">
        {{ $transaksiMasuk->appends(['tab' => 'masuk'])->links() }}
    </div>
    @endif
</div>

{{-- TAB: BARANG KELUAR --}}
<div id="panel-keluar" class="glass-panel rounded-2xl border border-outline-variant/30 overflow-hidden animate-fade-in-up hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-sm p-lg border-b border-outline-variant/20">
        <div>
            <h3 class="font-headline-sm text-on-surface">Riwayat Barang Keluar</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Data yang diinput oleh operator melalui aplikasi mobile</p>
        </div>
        <a href="/audit-trail" class="text-error text-sm font-label-bold hover:underline flex items-center gap-xs">
            <span class="material-symbols-outlined text-[16px]">open_in_new</span> Lihat Audit Trail
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/30 bg-surface-container/40">
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Waktu</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Kode Transaksi</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Barang</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px] text-right">Jumlah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sebelum</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sesudah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Operator</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiKeluar as $trx)
                <tr class="border-b border-outline-variant/10 hover:bg-surface-container/50 transition-colors">
                    <td class="py-3 px-4 text-sm text-on-surface-variant whitespace-nowrap">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-3 px-4 text-xs font-mono text-on-surface-variant">{{ $trx->kode_transaksi }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface">
                        {{ $trx->barang->nama_barang ?? '-' }}
                        <span class="text-xs text-on-surface-variant font-normal block">{{ $trx->barang->kode_barang ?? '' }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm font-mono font-bold text-error text-right">-{{ $trx->jumlah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant text-center">{{ $trx->stok_sebelum }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface text-center">{{ $trx->stok_sesudah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant">{{ $trx->user->name ?? 'Operator' }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant max-w-[180px] truncate" title="{{ $trx->keterangan }}">{{ $trx->keterangan ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-xl text-center text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined text-4xl block mb-sm opacity-30">output</span>
                        Belum ada transaksi barang keluar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksiKeluar->hasPages())
    <div class="p-md border-t border-outline-variant/20">
        {{ $transaksiKeluar->appends(['tab' => 'keluar'])->links() }}
    </div>
    @endif
</div>

{{-- TAB: MUTASI STOK --}}
<div id="panel-mutasi" class="glass-panel rounded-2xl border border-outline-variant/30 overflow-hidden animate-fade-in-up hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-sm p-lg border-b border-outline-variant/20">
        <div>
            <h3 class="font-headline-sm text-on-surface">Riwayat Mutasi Stok</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Data mutasi antar lokasi yang dilakukan oleh operator</p>
        </div>
        <a href="/audit-trail" class="text-sm font-label-bold hover:underline flex items-center gap-xs" style="color: var(--md-sys-color-tertiary, #cfbcff)">
            <span class="material-symbols-outlined text-[16px]">open_in_new</span> Lihat Audit Trail
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/30 bg-surface-container/40">
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Waktu</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Kode Transaksi</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Barang</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px] text-right">Jumlah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sebelum</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Stok Sesudah</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Operator</th>
                    <th class="py-3 px-4 font-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Keterangan / Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiMutasi as $trx)
                <tr class="border-b border-outline-variant/10 hover:bg-surface-container/50 transition-colors">
                    <td class="py-3 px-4 text-sm text-on-surface-variant whitespace-nowrap">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-3 px-4 text-xs font-mono text-on-surface-variant">{{ $trx->kode_transaksi }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface">
                        {{ $trx->barang->nama_barang ?? '-' }}
                        <span class="text-xs text-on-surface-variant font-normal block">{{ $trx->barang->kode_barang ?? '' }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm font-mono font-bold text-right" style="color: var(--md-sys-color-tertiary, #cfbcff)">{{ $trx->jumlah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant text-center">{{ $trx->stok_sebelum }}</td>
                    <td class="py-3 px-4 text-sm font-bold text-on-surface text-center">{{ $trx->stok_sesudah }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant">{{ $trx->user->name ?? 'Operator' }}</td>
                    <td class="py-3 px-4 text-sm text-on-surface-variant max-w-[200px] truncate" title="{{ $trx->keterangan }}">{{ $trx->keterangan ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-xl text-center text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined text-4xl block mb-sm opacity-30">sync_alt</span>
                        Belum ada transaksi mutasi stok.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksiMutasi->hasPages())
    <div class="p-md border-t border-outline-variant/20">
        {{ $transaksiMutasi->appends(['tab' => 'mutasi'])->links() }}
    </div>
    @endif
</div>

<script>
    function switchTab(tabName) {
        const panels = ['masuk', 'keluar', 'mutasi'];
        panels.forEach(t => {
            document.getElementById('panel-' + t).classList.add('hidden');
            const tab = document.getElementById('tab-' + t);
            tab.className = "px-lg py-sm font-label-bold text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center gap-sm";
        });

        document.getElementById('panel-' + tabName).classList.remove('hidden');

        const activeClasses = {
            masuk:  "px-lg py-sm font-label-bold text-primary border-b-2 border-primary transition-colors flex items-center gap-sm",
            keluar: "px-lg py-sm font-label-bold text-error border-b-2 border-error transition-colors flex items-center gap-sm",
            mutasi: "px-lg py-sm font-label-bold border-b-2 transition-colors flex items-center gap-sm",
        };
        document.getElementById('tab-' + tabName).className = activeClasses[tabName];
        if (tabName === 'mutasi') {
            document.getElementById('tab-mutasi').style.color = 'var(--md-sys-color-tertiary, #cfbcff)';
            document.getElementById('tab-mutasi').style.borderColor = 'var(--md-sys-color-tertiary, #cfbcff)';
        }
    }

    // Restore active tab from URL hash or default
    const hash = window.location.hash.replace('#', '') || 'masuk';
    if (['masuk', 'keluar', 'mutasi'].includes(hash)) switchTab(hash);
</script>
@endsection
