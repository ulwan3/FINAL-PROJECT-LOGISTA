<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #6750A4; padding-bottom: 10px; }
        .card-container { margin-top: 20px; }
        .card { display: inline-block; width: 30%; border: 1px solid #ddd; padding: 10px; text-align: center; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #6750A4; color: white; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 10px; background: #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Logista Warehouse Report</h1>
        <p>Status Gudang Pusat pada {{ $tanggal }}</p>
    </div>

    <div class="card-container">
        <div class="card">
            <small>Total Barang</small>
            <h2>{{ $total_barang }}</h2>
        </div>
        <div class="card">
            <small>Stok Menipis</small>
            <h2 style="color: #B3261E;">{{ $stok_menipis }}</h2>
        </div>
        <div class="card">
            <small>Kapasitas</small>
            <h2>{{ $kapasitas }}%</h2>
        </div>
    </div>

    <h3>Aktivitas Terakhir</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Jumlah Pesanan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aktivitas as $item)
            <tr>
                <td>{{ $item->barang->nama_barang }}</td>
                <td>{{ $item->qty_pesan }} pcs</td>
                <td><span class="status">{{ strtoupper($item->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>