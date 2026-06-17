<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peringatan Stok Logista</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 24px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 24px; border-radius: 12px;">
        <h2 style="margin-top: 0; color: #222;">
            Peringatan Stok Logista
        </h2>

        @if($statusAlert === 'habis')
            <p style="color: #d32f2f; font-weight: bold;">
                Status: STOK HABIS
            </p>
        @else
            <p style="color: #f57c00; font-weight: bold;">
                Status: STOK MENIPIS
            </p>
        @endif

        <p>Barang berikut membutuhkan perhatian:</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">Kode Barang</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                    {{ $barang->kode_barang }}
                </td>
            </tr>

            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">Nama Barang</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                    {{ $barang->nama_barang }}
                </td>
            </tr>

            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">Stok Saat Ini</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                    {{ $stokSaatIni }} {{ $barang->satuan }}
                </td>
            </tr>

            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">Stok Minimum</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                    {{ $barang->stok_minimum }} {{ $barang->satuan }}
                </td>
            </tr>
        </table>

        <p style="margin-top: 20px;">
            Silakan lakukan pengecekan atau restock barang melalui sistem Logista.
        </p>

        <p style="font-size: 12px; color: #777; margin-top: 24px;">
            Email ini dikirim otomatis oleh sistem Logista.
        </p>
    </div>
</body>
</html>