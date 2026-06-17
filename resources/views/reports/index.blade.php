@extends('layouts.app')

@section('header_action')
<button class="bg-primary text-on-primary font-label-bold px-lg py-2 rounded-lg hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(207,188,255,0.2)]">
    <span class="material-symbols-outlined text-[20px]">print</span>
    Cetak Laporan
</button>
@endsection

@section('content')
<!-- Header -->
<section class="flex flex-col gap-sm mb-md">
    <h2 class="font-headline-md text-on-surface">Laporan & Analitik (Reports)</h2>
    <p class="font-body-sm text-on-surface-variant">Analisis pergerakan barang, utilisasi gudang, dan nilai inventori.</p>
</section>

<!-- Tab Navigation -->
<div class="flex gap-md border-b border-outline-variant/30 mb-lg overflow-x-auto custom-scrollbar pb-1">
    <button onclick="showTab('ringkasan')" id="tabRingkasanBtn" class="pb-2 border-b-2 border-primary text-primary font-label-bold px-2 whitespace-nowrap">Ringkasan Eksekutif</button>
    <button onclick="showTab('pergerakan')" id="tabPergerakanBtn" class="pb-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface font-label-bold px-2 whitespace-nowrap transition-colors">Pergerakan Stok (In/Out)</button>
</div>

<!-- KONTEN TAB 1: Ringkasan Eksekutif -->
<div id="kontenRingkasan" class="tab-content">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-md mb-lg">
        <div class="glass-panel p-lg rounded-xl border-t-2 border-primary">
            <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Total Barang Masuk (Bulan Ini)</h4>
            <div class="flex items-baseline gap-2">
                <span class="font-display text-[32px] text-on-surface leading-none">{{ number_format($masuk) }}</span>
                <span class="text-body-sm text-on-surface-variant">Unit</span>
            </div>
        </div>

        <div class="glass-panel p-lg rounded-xl border-t-2 border-secondary">
            <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Total Barang Keluar (Bulan Ini)</h4>
            <div class="flex items-baseline gap-2">
                <span class="font-display text-[32px] text-on-surface leading-none">{{ number_format($keluar) }}</span>
                <span class="text-body-sm text-on-surface-variant">Unit</span>
            </div>
        </div>

        <div class="glass-panel p-lg rounded-xl border-t-2 border-tertiary">
            <h4 class="font-label-bold text-on-surface-variant mb-2 uppercase tracking-wider">Rata-rata Lama Stok (Turnover)</h4>
            <div class="flex items-baseline gap-2">
                <span class="font-display text-[32px] text-on-surface leading-none">14</span>
                <span class="text-body-sm text-on-surface-variant">Hari</span>
            </div>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-xl flex flex-col mb-lg w-full"> <div class="flex justify-between items-center mb-md">
        <h3 class="font-headline-sm text-on-surface">Grafik Barang Masuk vs Keluar (30 Hari Terakhir)</h3>
    </div>
    @if($totalTransaksi == 0)
        <div class="flex flex-col items-center justify-center h-[320px] text-on-surface-variant">
            <span class="material-symbols-outlined text-[60px] mb-4">bar_chart</span>
            <p class="text-center">Belum ada data transaksi.</p>
        </div>
    @else
        <div class="w-full h-[320px] relative">
            <canvas id="stockChart"></canvas>
        </div>
    @endif
</div>

    <div class="glass-panel p-lg rounded-xl">
        <div class="flex items-center gap-2 mb-md">
            <span class="material-symbols-outlined text-secondary">trending_down</span>
            <h3 class="font-headline-sm text-on-surface">Top 5 Barang Paling Sering Keluar</h3>
            <span class="text-xs text-on-surface-variant ml-2">(Bulan Ini)</span>
        </div>
        @if($topBarangKeluar->count() > 0)
            <div class="space-y-3">
                @foreach($topBarangKeluar as $item)
                <div class="flex justify-between items-center p-4 rounded-xl bg-surface-container/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-secondary">package_2</span>
                        </div>
                        <div>
                            <div class="text-on-surface font-body-md font-bold">{{ $item->nama_barang }}</div>
                            <div class="text-on-surface-variant text-xs">{{ $item->satuan }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-secondary font-bold text-2xl">{{ number_format($item->total_keluar) }}</div>
                        <div class="text-on-surface-variant text-xs">keluar</div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 text-center text-on-surface-variant text-sm border-t border-outline-variant/30">
                Total barang keluar bulan ini: <span class="text-secondary font-bold">{{ number_format($topBarangKeluar->sum('total_keluar')) }}</span> unit
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] mb-2">inventory</span>
                <p class="text-center">Belum ada data barang keluar.</p>
            </div>
        @endif
    </div>
</div>

<!-- KONTEN TAB 2: Pergerakan Stok (In/Out) -->
<div id="kontenPergerakan" class="tab-content" style="display: none;">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-md mb-lg">
        <div class="glass-panel p-lg rounded-xl border-t-2 border-primary">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary text-[32px]">inventory</span>
                <h4 class="font-label-bold text-on-surface-variant uppercase tracking-wider">Total Pergerakan</h4>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-3">
                <div class="text-center">
                    <p class="text-on-surface-variant text-sm">Barang Masuk</p>
                    <p class="text-primary font-bold text-3xl">{{ number_format($masuk) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-on-surface-variant text-sm">Barang Keluar</p>
                    <p class="text-secondary font-bold text-3xl">{{ number_format($keluar) }}</p>
                </div>
            </div>
        </div>

        <div class="glass-panel p-lg rounded-xl border-t-2 border-tertiary">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-tertiary text-[32px]">swap_horiz</span>
                <h4 class="font-label-bold text-on-surface-variant uppercase tracking-wider">Selisih Masuk - Keluar</h4>
            </div>
            <div class="text-center mt-3">
                @php $net = $masuk - $keluar; @endphp
                <p class="text-on-surface-variant text-sm">Selisih</p>
                <p class="{{ $net >= 0 ? 'text-green-400' : 'text-error' }} font-bold text-4xl">{{ number_format(abs($net)) }}</p>
                <p class="text-on-surface-variant text-xs mt-1">{{ $net >= 0 ? 'Stok bertambah' : 'Stok berkurang' }}</p>
            </div>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-xl flex flex-col mb-lg">
        <div class="flex justify-between items-center mb-md">
            <h3 class="font-headline-sm text-on-surface">Pergerakan Stok per Minggu</h3>
            <span class="text-xs text-on-surface-variant">4 minggu terakhir</span>
        </div>
        @if($totalTransaksi == 0)
            <div class="flex flex-col items-center justify-center h-[250px] text-on-surface-variant">
                <span class="material-symbols-outlined text-[60px] mb-4">bar_chart</span>
                <p class="text-center">Belum ada data transaksi.</p>
            </div>
        @else
            <div class="w-full h-[250px]">
                <canvas id="pergerakanChart"></canvas>
            </div>
        @endif
    </div>

    <!-- Tabel Detail Pergerakan -->
    <div class="glass-panel p-lg rounded-xl">
        <div class="flex items-center gap-2 mb-md">
            <span class="material-symbols-outlined text-primary">table_chart</span>
            <h3 class="font-headline-sm text-on-surface">Detail Pergerakan per Minggu</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-outline-variant/30">
                        <th class="text-left py-3 px-2 text-on-surface-variant font-label-bold text-sm">Minggu</th>
                        <th class="text-center py-3 px-2 text-on-surface-variant font-label-bold text-sm">Barang Masuk</th>
                        <th class="text-center py-3 px-2 text-on-surface-variant font-label-bold text-sm">Barang Keluar</th>
                        <th class="text-center py-3 px-2 text-on-surface-variant font-label-bold text-sm">Selisih</th>
                        <th class="text-center py-3 px-2 text-on-surface-variant font-label-bold text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chartData['labels'] as $index => $label)
                        @php
                            $masukMinggu = $chartData['masuk'][$index];
                            $keluarMinggu = $chartData['keluar'][$index];
                            $netMinggu = $masukMinggu - $keluarMinggu;
                            $statusClass = $netMinggu >= 0 ? 'text-green-400' : 'text-error';
                            $statusIcon = $netMinggu >= 0 ? '▲' : '▼';
                        @endphp
                        <tr class="border-b border-outline-variant/20 hover:bg-surface-container/20 transition-all">
                            <td class="py-3 px-2 font-bold text-on-surface">{{ $label }}</td>
                            <td class="py-3 px-2 text-center text-primary">{{ number_format($masukMinggu) }}</td>
                            <td class="py-3 px-2 text-center text-secondary">{{ number_format($keluarMinggu) }}</td>
                            <td class="py-3 px-2 text-center {{ $statusClass }}">{{ number_format(abs($netMinggu)) }}</td>
                            <td class="py-3 px-2 text-center {{ $statusClass }}">{{ $statusIcon }} {{ $netMinggu >= 0 ? 'Tambah' : 'Kurang' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Ringkasan di bawah tabel (TANPA RASIO) -->
        <div class="mt-4 p-3 rounded-xl bg-primary/10 border border-primary/20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-center">
                <div>
                    <p class="text-on-surface-variant text-xs">Total Masuk</p>
                    <p class="text-primary font-bold text-lg">{{ number_format($masuk) }}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant text-xs">Total Keluar</p>
                    <p class="text-secondary font-bold text-lg">{{ number_format($keluar) }}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant text-xs">Selisih</p>
                    <p class="{{ $net >= 0 ? 'text-green-400' : 'text-error' }} font-bold text-lg">{{ number_format(abs($net)) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(tabName) {
        document.getElementById('kontenRingkasan').style.display = 'none';
        document.getElementById('kontenPergerakan').style.display = 'none';
        const ringkasanBtn = document.getElementById('tabRingkasanBtn');
        const pergerakanBtn = document.getElementById('tabPergerakanBtn');
        ringkasanBtn.classList.remove('border-primary', 'text-primary');
        ringkasanBtn.classList.add('border-transparent', 'text-on-surface-variant');
        pergerakanBtn.classList.remove('border-primary', 'text-primary');
        pergerakanBtn.classList.add('border-transparent', 'text-on-surface-variant');
        if (tabName === 'ringkasan') {
            document.getElementById('kontenRingkasan').style.display = 'block';
            ringkasanBtn.classList.remove('border-transparent', 'text-on-surface-variant');
            ringkasanBtn.classList.add('border-primary', 'text-primary');
        } else {
            document.getElementById('kontenPergerakan').style.display = 'block';
            pergerakanBtn.classList.remove('border-transparent', 'text-on-surface-variant');
            pergerakanBtn.classList.add('border-primary', 'text-primary');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = @json($chartData['labels']);
        const dataMasuk = @json($chartData['masuk']);
        const dataKeluar = @json($chartData['keluar']);
        
        const ctx = document.getElementById('stockChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Barang Masuk', data: dataMasuk, backgroundColor: 'rgba(34, 197, 94, 0.7)', borderColor: 'rgb(34, 197, 94)', borderWidth: 1, borderRadius: 4, barPercentage: 0.65, categoryPercentage: 0.7 },
                        { label: 'Barang Keluar', data: dataKeluar, backgroundColor: 'rgba(239, 68, 68, 0.7)', borderColor: 'rgb(239, 68, 68)', borderWidth: 1, borderRadius: 4, barPercentage: 0.65, categoryPercentage: 0.7 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: 'top', labels: { color: '#e2e8f0', font: { size: 10 } } }, tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': ' + ctx.raw.toLocaleString() + ' Unit' } } },
                    scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 9 } } }, x: { ticks: { color: '#94a3b8', font: { size: 9 } } } }
                }
            });
        }
        
        const ctx2 = document.getElementById('pergerakanChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Barang Masuk', data: dataMasuk, borderColor: 'rgb(34, 197, 94)', backgroundColor: 'rgba(34, 197, 94, 0.1)', tension: 0.3, fill: true, pointRadius: 4 },
                        { label: 'Barang Keluar', data: dataKeluar, borderColor: 'rgb(239, 68, 68)', backgroundColor: 'rgba(239, 68, 68, 0.1)', tension: 0.3, fill: true, pointRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: 'top', labels: { color: '#e2e8f0', font: { size: 10 } } }, tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': ' + ctx.raw.toLocaleString() + ' Unit' } } },
                    scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 9 } } }, x: { ticks: { color: '#94a3b8', font: { size: 9 } } } }
                }
            });
        }
    });
</script>
@endsection