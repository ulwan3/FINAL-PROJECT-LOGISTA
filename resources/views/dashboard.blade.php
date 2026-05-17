@extends('layouts.app')

@section('content')
<section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
    <div>
        <h2 class="font-headline-lg text-on-surface tracking-tight">Overview Hari Ini</h2>
        <p class="font-body-md text-on-surface-variant mt-1">Status gudang pusat pada {{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY, HH:mm') }} WIB</p>
    </div>
    <div class="flex gap-sm w-full md:w-auto">
        <a href="{{ route('export.pdf') }}" 
        role="button"
        class="flex-1 md:flex-none glass border border-outline-variant/30 text-on-surface px-md py-2 rounded-lg font-label-bold hover:bg-surface-container-high transition-colors flex items-center justify-center gap-2 no-underline">
            <span class="material-symbols-outlined text-[20px]">download</span>
            Export
        </a>

        <button onclick="openModalPesanan()" class="flex-1 md:flex-none bg-primary text-on-primary px-md py-2 rounded-lg font-label-bold hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(207,188,255,0.2)]">
            <span class="material-symbols-outlined text-[20px]">local_shipping</span>
            Pesan ke Supplier
        </button>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md mt-lg">
    <div class="glass-panel p-lg rounded-2xl flex flex-col gap-md relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-xl group-hover:bg-primary/20 transition-all"></div>
        <div class="flex justify-between items-start">
            <span class="font-label-bold text-on-surface-variant uppercase tracking-wider">Total Barang</span>
            <span class="material-symbols-outlined text-primary">inventory_2</span>
        </div>
        <div>
            <div class="font-display text-[32px] text-on-surface leading-none">{{ number_format($totalBarang) }}</div>
            <div class="flex items-center gap-xs mt-sm text-green-400 font-label-bold">
                <span class="material-symbols-outlined text-[16px]">inventory</span>
                <span>Data Master</span>
            </div>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-2xl flex flex-col gap-md relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-tertiary/10 rounded-full blur-xl group-hover:bg-tertiary/20 transition-all"></div>
        <div class="flex justify-between items-start">
            <span class="font-label-bold text-on-surface-variant uppercase tracking-wider">Stok Menipis</span>
            <span class="material-symbols-outlined text-tertiary">warning</span>
        </div>
        <div>
            <div class="font-display text-[32px] text-tertiary leading-none">{{ number_format($stokMenipis) }}</div>
            <div class="flex items-center gap-xs mt-sm text-tertiary font-label-bold">
                <span>Butuh restock segera</span>
            </div>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-2xl flex flex-col gap-md relative overflow-hidden group border-error/30">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-error/10 rounded-full blur-xl group-hover:bg-error/20 transition-all"></div>
        <div class="flex justify-between items-start">
            <span class="font-label-bold text-on-surface-variant uppercase tracking-wider">Kapasitas Gudang</span>
            <span class="material-symbols-outlined text-error">warehouse</span>
        </div>
        <div>
            <div class="font-display text-[32px] text-on-surface leading-none">{{ round($persentaseGudang) }}%</div>
            <div class="w-full bg-surface-container-high rounded-full h-1.5 mt-sm overflow-hidden">
                <div class="bg-error h-1.5 rounded-full transition-all duration-500" style="width: {{ $persentaseGudang }}%"></div>
            </div>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-2xl flex flex-col gap-md relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary/10 rounded-full blur-xl group-hover:bg-secondary/20 transition-all"></div>
        <div class="flex justify-between items-start">
            <span class="font-label-bold text-on-surface-variant uppercase tracking-wider">Transaksi Harian</span>
            <span class="material-symbols-outlined text-secondary">swap_horiz</span>
        </div>
        <div>
            <div class="font-display text-[32px] text-on-surface leading-none">{{ number_format($totalTransaksiHarian) }}</div>
            <div class="flex items-center gap-xs mt-sm text-on-surface-variant font-label-bold">
                <span>{{ $transaksiHarianMasuk }} Masuk / {{ $transaksiHarianKeluar }} Keluar</span>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 lg:grid-cols-3 gap-lg mt-lg">
    <div class="lg:col-span-2 glass-panel p-lg rounded-2xl flex flex-col">
        <div class="flex justify-between items-center mb-lg">
            <h3 class="font-headline-sm text-on-surface">Tren Pergerakan Stok</h3>
            <select id="chartFilter" class="bg-surface-container-low border border-outline-variant/30 text-on-surface text-body-sm rounded-lg px-3 py-1 outline-none cursor-pointer">
                <option value="minggu">Minggu Ini</option>
                <option value="bulan">Bulan Ini</option>
            </select>
        </div>
        
        <!-- Ganti div batang statik dengan canvas ini -->
        <div class="flex-1 h-64 relative">
            <canvas id="stockTrendChart"></canvas>
        </div>
    </div>

    <div class="glass-panel p-lg rounded-2xl flex flex-col h-full">
        <div class="flex justify-between items-center mb-lg">
            <h3 class="font-headline-sm text-on-surface">Aktivitas Terakhir</h3>
            <a href="{{ route('riwayat.index') }}" class="text-primary hover:underline text-body-sm font-label-bold relative z-50">
                Lihat Semua
            </a>
        </div>
        
        <div class="flex-1 space-y-4 overflow-y-auto custom-scrollbar">
          @forelse($recentActivities as $activity)
                <div class="flex justify-between items-start border-b border-outline-variant/10 pb-3">
                    <div>
                        <p class="font-body-xs text-on-surface-variant/70 mt-0.5">
                            Supplier: <span class="text-primary/90 font-medium">{{ $activity->supplier->nama_supplier ?? 'Tanpa Supplier' }}</span>
                        </p>
                        <p class="font-body-md text-on-surface font-bold">
                            {{-- Gunakan $activity --}}
                            {{ $activity->barang->nama_barang ?? 'Barang Terhapus' }}
                        </p>
                        <p class="font-body-sm text-on-surface-variant">
                            Pesanan: <span class="text-white">{{ $activity->jumlah_pesan ?? 0 }}</span> pcs
                        </p>
                    </div>
                    
                    {{-- Badge Status --}}
                    <span class="text-[10px] px-2 py-1 rounded-md font-label-bold uppercase border 
                        {{ ($activity->status == 'pending') ? 'border-yellow-500/50 text-yellow-400' : 'border-green-500/50 text-green-400' }}">
                        {{ $activity->status ?? 'pending' }}
                    </span>
                </div>
            @empty
                <p class="text-on-surface-variant text-center py-4">Belum ada aktivitas.</p>
            @endforelse {{-- Pastikan tertutup dengan @endforelse, bukan @endforeach --}}
        </div>
    </div>
</section>

<div id="modalPesanan" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="glass-panel p-lg rounded-2xl w-full max-w-md border border-outline-variant/30 shadow-2xl bg-surface-container">
        <div class="flex justify-between items-center mb-lg">
            <h3 class="font-headline-sm text-on-surface">Form Pesanan Supplier</h3>
            <button onclick="closeModalPesanan()" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('transaksi.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] text-on-surface-variant mb-2 uppercase font-label-bold">Pilih Barang</label>
                <select name="barang_id" class="w-full bg-surface-container-low border border-outline-variant/30 text-white rounded-lg px-4 py-2 outline-none">
                    @foreach(\App\Models\Barang::all() as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_barang }} (Stok: {{ $b->stok }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-on-surface-variant mb-2 uppercase font-label-bold">Pilih Supplier</label>
                <select name="supplier_id" class="w-full bg-surface-container-low border border-outline-variant/30 text-white rounded-lg px-4 py-2 outline-none">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-on-surface-variant mb-2 uppercase font-label-bold">Jumlah Pesanan</label>
                <input type="number" name="jumlah" min="1" required class="w-full bg-surface-container-low border border-outline-variant/30 text-on-surface rounded-lg px-4 py-2 outline-none">
            </div>
            <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-label-bold hover:brightness-110 active:scale-95 transition-all">
                Buat Pesanan Sekarang
            </button>
        </form>
    </div>
</div>

<script>
    function openModalPesanan() {
        document.getElementById('modalPesanan').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModalPesanan() {
        document.getElementById('modalPesanan').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    window.onclick = function(event) {
        let modal = document.getElementById('modalPesanan');
        if (event.target == modal) closeModalPesanan();
    }
</script>

<!-- Load library melalui CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('stockTrendChart').getContext('2d');
        const filterSelect = document.getElementById('chartFilter');
        let stockChart;

        // Fungsi untuk membuat/mengganti grafik
        function initChart(labels, masuk, keluar) {
            if (stockChart) {
                stockChart.destroy(); // Hancurkan chart lama sebelum membuat yang baru
            }

            stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Masuk',
                            data: masuk,
                            backgroundColor: '#00d2d3',
                            borderRadius: 4,
                        },
                        {
                            label: 'Keluar',
                            data: keluar,
                            backgroundColor: '#ff9ff3',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#a29bfe' } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#a29bfe' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#a29bfe' }
                        }
                    }
                }
            });
        }

        // Fungsi untuk mengambil data menggunakan AJAX Fetch API
        function fetchChartData(filterValue) {
            fetch(`/dashboard/chart-data?filter=${filterValue}`)
                .then(response => response.json())
                .then(data => {
                    initChart(data.labels, data.stok_masuk, data.stok_keluar);
                })
                .catch(error => console.error('Error fetching chart data:', error));
        }

        // Jalankan pertama kali saat halaman dibuka (default: minggu)
        fetchChartData('minggu');

        // Event listener saat dropdown diganti
        filterSelect.addEventListener('change', function() {
            fetchChartData(this.value);
        });
    });
</script>

@endsection