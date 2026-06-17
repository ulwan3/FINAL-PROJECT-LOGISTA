<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Gudang Logista</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 10px;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #6750A4;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: #6750A4;
            font-size: 18px;
            margin-bottom: 3px;
        }
        
        .header p {
            color: #666;
            font-size: 9px;
        }
        
        .card-container {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .card {
            flex: 1;
            background: #f8f6fc;
            border: 1px solid #e0d6f0;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }
        
        .card small {
            color: #666;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card h2 {
            color: #6750A4;
            font-size: 20px;
            margin-top: 3px;
        }
        
        .card .danger {
            color: #B3261E;
        }
        
        .card .warning {
            color: #f4b400;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #6750A4;
            border-left: 3px solid #6750A4;
            padding-left: 8px;
            margin: 15px 0 8px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            text-align: left;
            vertical-align: middle;
        }
        
        th {
            background-color: #6750A4;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-aman {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-menipis {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-habis {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        
        .note {
            font-size: 7px;
            color: #999;
            margin-top: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LOGISTA WAREHOUSE REPORT</h1>
        <p>Status Gudang Pusat pada {{ $tanggal }}</p>
    </div>

    <div class="card-container">
        <div class="card">
            <small>TOTAL STOK</small>
            <h2>{{ number_format($total_stok) }} pcs</h2>
        </div>
        <div class="card">
            <small>TOTAL SKU</small>
            <h2>{{ $total_barang }}</h2>
        </div>
        <div class="card">
            <small>STOK MENIPIS</small>
            <h2 class="warning">{{ $stok_menipis }}</h2>
        </div>
        <div class="card">
            <small>STOK HABIS</small>
            <h2 class="danger">{{ $stok_habis }}</h2>
        </div>
    </div>

    <div class="section-title">
        SKU & DAFTAR BARANG
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="10%">SKU/Kode</th>
                <th width="12%">Nama Barang</th>
                <th width="10%">Kategori</th>
                <th width="8%">Stok</th>
                <th width="8%">Min</th>
                <th width="10%">Stok Masuk*</th>
                <th width="10%">Stok Keluar*</th>
                <th width="8%">Mutasi*</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barang as $item)
            <tr>
                <td>{{ $item->kode_barang ?? '-' }}</td>
                <td><strong>{{ $item->nama_barang }}</strong></td>
                <td>{{ $item->kategori_nama ?? '-' }}</td>
                <td>{{ number_format($item->stok) }} {{ $item->satuan ?? 'pcs' }}</td>
                <td>{{ number_format($item->stok_minimum ?? 0) }} {{ $item->satuan ?? 'pcs' }}</td>
                <td style="text-align: right;">{{ number_format($item->total_stok_masuk ?? 0) }}</td>
                <td style="text-align: right;">{{ number_format($item->total_stok_keluar ?? 0) }}</td>
                <td style="text-align: right;">
                    @php
                        $mutasi = ($item->total_stok_masuk ?? 0) - ($item->total_stok_keluar ?? 0);
                    @endphp
                    <span style="{{ $mutasi >= 0 ? 'color: green;' : 'color: red;' }}">
                        {{ $mutasi >= 0 ? '+' : '' }}{{ number_format($mutasi) }}
                    </span>
                </td>
                <td>
                    @php
                        if ($item->stok <= 0) {
                            $status = 'Habis';
                        } elseif ($item->stok <= $item->stok_minimum) {
                            $status = 'Menipis';
                        } else {
                            $status = 'Aman';
                        }
                    @endphp
                    @if($status == 'Aman')
                        <span class="status-aman">Aman</span>
                    @elseif($status == 'Menipis')
                        <span class="status-menipis">Menipis</span>
                    @else
                        <span class="status-habis">Habis</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data barang</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="note">
        * Stok Masuk/Keluar/Mutasi dihitung dari seluruh transaksi (barang_masuk/barang_keluar) dan penerimaan pesanan yang sudah diverifikasi.
    </div>

    <div class="footer">
        Laporan ini digenerate secara otomatis oleh sistem Logista Warehouse Management<br>
        &copy; {{ date('Y') }} Logista - Semua hak dilindungi
    </div>
</body>
</html>