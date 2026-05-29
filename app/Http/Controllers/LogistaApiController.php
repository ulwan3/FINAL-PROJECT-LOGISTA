<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Exception;

class LogistaApiController extends Controller
{
    private function errorResponse(Exception $err, $message = 'Terjadi kesalahan server', $status = 500)
{
    \Log::error('LOGISTA API ERROR: ' . $err->getMessage(), [
        'file' => $err->getFile(),
        'line' => $err->getLine(),
        'trace' => $err->getTraceAsString(),
    ]);

    $code = $err->getCode();

    if ($code >= 400 && $code <= 599) {
        $status = $code;
        $message = $err->getMessage();
    }

    return response()->json([
        'success' => false,
        'message' => $message,
        'error' => $err->getMessage(),
    ], $status);
}

    private function saveStockSnapshot()
    {
        $row = DB::selectOne("
            SELECT COALESCE(SUM(stok_gudang + stok_rak), 0) AS total_stok
            FROM barangs
        ");

        DB::insert("
            INSERT INTO stock_snapshots (total_stok, created_at)
            VALUES (?, NOW())
        ", [$row->total_stok]);
    }

    private function createNotification($title, $message, $type, $pemesananId = null)
    {
        DB::insert("
            INSERT INTO notifications
            (title, message, type, pemesanan_id, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ", [$title, $message, $type, $pemesananId]);
    }

    private function closePemesananNotification($pemesananId)
    {
        DB::update("
            UPDATE notifications
            SET is_read = 1
            WHERE type = 'pemesanan'
            AND pemesanan_id = ?
        ", [$pemesananId]);
    }

    public function api()
    {
        return response()->json([
            'success' => true,
            'message' => 'API Logista jalan',
        ]);
    }

    public function register(Request $request)
    {
        try {
            $name = $request->name;
            $email = $request->email;
            $username = $request->username;
            $password = $request->password;

            if (!$name || !$email || !$username || !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak lengkap',
                ], 400);
            }

            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Register berhasil',
            ], 201);

        } catch (QueryException $err) {
            if (($err->errorInfo[1] ?? null) == 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau username sudah terdaftar',
                ], 400);
            }

            return $this->errorResponse($err);
        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function login(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;

            if (!$email || !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email dan password wajib diisi',
                ], 400);
            }

            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ada',
                ], 404);
            }

            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                ],
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function lupaPassword(Request $request)
    {
        try {
            $email = $request->email;

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email wajib diisi',
                ], 400);
            }

            $cooldownKey = 'otp_cooldown_' . md5($email);
            $otpKey = 'otp_reset_' . md5($email);

            if (Cache::has($cooldownKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tunggu 60 detik sebelum kirim ulang OTP',
                ], 429);
            }

            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan',
                ], 404);
            }

            $otp = random_int(1000, 9999);

            Cache::put($otpKey, [
                'code' => $otp,
                'verified' => false,
            ], now()->addMinutes(5));

            Cache::put($cooldownKey, true, now()->addSeconds(60));

            Mail::raw("Kode OTP reset password kamu adalah: {$otp}. Berlaku 5 menit.", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Kode OTP Reset Password');
            });

            return response()->json([
                'success' => true,
                'message' => 'OTP dikirim ke email',
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function masukkanOtp(Request $request)
    {
        $email = $request->email;
        $otp = $request->otp;

        $otpKey = 'otp_reset_' . md5($email);
        $data = Cache::get($otpKey);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ada',
            ], 400);
        }

        if ((string) $data['code'] !== (string) $otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP salah',
            ], 400);
        }

        $data['verified'] = true;
        Cache::put($otpKey, $data, now()->addMinutes(5));

        return response()->json([
            'success' => true,
            'message' => 'OTP valid',
        ]);
    }

    public function resetPassword(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;

            $otpKey = 'otp_reset_' . md5($email);
            $data = Cache::get($otpKey);

            if (!$data || empty($data['verified'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP belum diverifikasi',
                ], 400);
            }

            DB::table('users')
                ->where('email', $email)
                ->update([
                    'password' => Hash::make($password),
                ]);

            Cache::forget($otpKey);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah',
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function barang()
    {
        try {
            $rows = DB::select("
                SELECT *, (stok_gudang + stok_rak) AS total_stok
                FROM barangs
                ORDER BY id DESC
            ");

            return response()->json([
                'success' => true,
                'data' => $rows,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function detailBarang($id)
    {
        try {
            $row = DB::selectOne("
                SELECT *, (stok_gudang + stok_rak) AS total_stok
                FROM barangs
                WHERE id = ?
            ", [$id]);

            if (!$row) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $row,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function tambahPemesanan(Request $request)
    {
        try {
            $barangId = $request->barang_id;
            $jumlahPesan = $request->jumlah_pesan;
            $namaSupplier = $request->nama_supplier;

            if (!$barangId || !$jumlahPesan || !$namaSupplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data belum lengkap',
                ], 400);
            }

            $result = DB::transaction(function () use ($barangId, $jumlahPesan, $namaSupplier) {
                $barang = DB::selectOne("
                    SELECT id, nama_barang, kode_barang, satuan
                    FROM barangs
                    WHERE id = ?
                ", [$barangId]);

                if (!$barang) {
                    throw new Exception('Barang tidak ditemukan', 404);
                }

                DB::insert("
                    INSERT INTO pemesanan_barang
                    (barang_id, jumlah_pesan, nama_supplier, status, created_at)
                    VALUES (?, ?, ?, 'pending', NOW())
                ", [$barangId, $jumlahPesan, $namaSupplier]);

                $pemesananId = DB::getPdo()->lastInsertId();

                $satuan = $barang->satuan ?: 'pcs';

                $this->createNotification(
                    'ðŸ“¦ Pemesanan Baru',
                    "{$barang->nama_barang} dipesan sebanyak {$jumlahPesan} {$satuan} dari supplier {$namaSupplier}",
                    'pemesanan',
                    $pemesananId
                );

                return [
                    'pemesanan_id' => $pemesananId,
                    'barang' => $barang->nama_barang,
                    'qty' => $jumlahPesan,
                    'supplier' => $namaSupplier,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil ditambahkan',
                'data' => $result,
            ], 201);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

   public function pemesanan()
{
    try {
        $rows = DB::select("
            SELECT
                p.*,
                b.nama_barang,
                b.kode_barang,
                b.satuan,
                COALESCE(p.nama_supplier, s.nama_supplier, '-') AS nama_supplier
            FROM pemesanan_barang p
            LEFT JOIN barangs b ON p.barang_id = b.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.id DESC
        ");

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);

    } catch (Exception $err) {
        return $this->errorResponse($err);
    }
}

    public function verifikasiBarang(Request $request)
    {
        try {
            $userId = $request->user_id;
            $pemesananId = $request->pemesanan_id;
            $barangId = $request->barang_id;
            $qty = (int) ($request->qty_diterima ?? 0);
            $status = $request->status;
            $catatan = $request->catatan;

            if (!$userId || !$pemesananId || !$barangId || !$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data belum lengkap',
                ], 400);
            }

            if (!in_array($status, ['diterima', 'ditolak'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status tidak valid',
                ], 400);
            }

            if ($status === 'diterima' && $qty <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Qty diterima harus lebih dari 0',
                ], 400);
            }

            DB::transaction(function () use ($userId, $pemesananId, $barangId, $qty, $status, $catatan) {
                $barang = DB::selectOne("
                    SELECT id, stok_gudang, stok_rak
                    FROM barangs
                    WHERE id = ?
                ", [$barangId]);

                if (!$barang) {
                    throw new Exception('Barang tidak ditemukan', 404);
                }

                $totalSebelum = (int) $barang->stok_gudang + (int) $barang->stok_rak;
                $gudangSebelum = (int) $barang->stok_gudang;
                $rakSebelum = (int) $barang->stok_rak;

                $totalSesudah = $status === 'diterima' ? $totalSebelum + $qty : $totalSebelum;
                $gudangSesudah = $status === 'diterima' ? $gudangSebelum + $qty : $gudangSebelum;
                $rakSesudah = $rakSebelum;

                if ($status === 'diterima') {
                    DB::update("
                        UPDATE barangs
                        SET stok_gudang = stok_gudang + ?
                        WHERE id = ?
                    ", [$qty, $barangId]);
                }

                DB::update("
                    UPDATE pemesanan_barang
                    SET status = ?, qty_diterima = ?, catatan = ?, verified_at = NOW()
                    WHERE id = ?
                ", [$status, $qty, $catatan ?: null, $pemesananId]);

                DB::insert("
                    INSERT INTO transaksis
                    (
                        user_id,
                        jenis,
                        barang_id,
                        jumlah,
                        total_stok_sebelum,
                        total_stok_sesudah,
                        stok_gudang_sebelum,
                        stok_gudang_sesudah,
                        stok_rak_sebelum,
                        stok_rak_sesudah,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ", [
                    $userId,
                    $status === 'diterima' ? 'barang_masuk' : 'barang_ditolak',
                    $barangId,
                    $qty,
                    $totalSebelum,
                    $totalSesudah,
                    $gudangSebelum,
                    $gudangSesudah,
                    $rakSebelum,
                    $rakSesudah,
                    $catatan ?: ($status === 'diterima' ? 'Barang masuk diverifikasi' : 'Barang ditolak'),
                ]);

                $this->closePemesananNotification($pemesananId);
            });

            $this->saveStockSnapshot();

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi barang berhasil',
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function barangKeluar(Request $request)
    {
        try {
            $userId = $request->user_id;
            $barangId = $request->barang_id;
            $qty = (int) ($request->qty_keluar ?? 0);
            $tujuan = $request->tujuan;
            $catatan = $request->catatan;

            if (!$userId || !$barangId || !$qty || !$tujuan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data belum lengkap',
                ], 400);
            }

            DB::transaction(function () use ($userId, $barangId, $qty, $tujuan, $catatan) {
                $barang = DB::selectOne("
                    SELECT *
                    FROM barangs
                    WHERE id = ?
                ", [$barangId]);

                if (!$barang) {
                    throw new Exception('Barang tidak ditemukan', 404);
                }

                $totalSebelum = (int) $barang->stok_gudang + (int) $barang->stok_rak;
                $gudangSebelum = (int) $barang->stok_gudang;
                $rakSebelum = (int) $barang->stok_rak;

                if ($qty > $rakSebelum) {
                    throw new Exception('Stok rak tidak mencukupi', 400);
                }

                $rakSesudah = $rakSebelum - $qty;
                $totalSesudah = $totalSebelum - $qty;

                DB::update("
                    UPDATE barangs
                    SET stok_rak = ?
                    WHERE id = ?
                ", [$rakSesudah, $barangId]);

                DB::insert("
                    INSERT INTO barang_keluar
                    (barang_id, qty_keluar, tujuan, catatan)
                    VALUES (?, ?, ?, ?)
                ", [$barangId, $qty, $tujuan, $catatan ?: null]);

                DB::insert("
                    INSERT INTO transaksis
                    (
                        user_id,
                        jenis,
                        barang_id,
                        jumlah,
                        total_stok_sebelum,
                        total_stok_sesudah,
                        stok_gudang_sebelum,
                        stok_gudang_sesudah,
                        stok_rak_sebelum,
                        stok_rak_sesudah,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (?, 'barang_keluar', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ", [
                    $userId,
                    $barangId,
                    $qty,
                    $totalSebelum,
                    $totalSesudah,
                    $gudangSebelum,
                    $gudangSebelum,
                    $rakSebelum,
                    $rakSesudah,
                    $catatan ?: $tujuan,
                ]);
            });

            $this->saveStockSnapshot();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dikeluarkan',
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function mutasiBarang(Request $request)
    {
        try {
            $userId = $request->user_id;
            $barangId = $request->barang_id;
            $qty = (int) ($request->qty_mutasi ?? 0);
            $lokasiAsal = $request->lokasi_asal;
            $lokasiTujuan = $request->lokasi_tujuan;
            $catatan = $request->catatan;

            if (!$userId || !$barangId || !$qty || !$lokasiAsal || !$lokasiTujuan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data belum lengkap',
                ], 400);
            }

            DB::transaction(function () use ($userId, $barangId, $qty, $lokasiAsal, $lokasiTujuan, $catatan) {
                $barang = DB::selectOne("
                    SELECT *
                    FROM barangs
                    WHERE id = ?
                ", [$barangId]);

                if (!$barang) {
                    throw new Exception('Barang tidak ditemukan', 404);
                }

                $totalSebelum = (int) $barang->stok_gudang + (int) $barang->stok_rak;
                $gudangSebelum = (int) $barang->stok_gudang;
                $rakSebelum = (int) ($barang->stok_rak ?? 0);

                if ($qty > $gudangSebelum) {
                    throw new Exception('Stok gudang tidak mencukupi', 400);
                }

                $gudangSesudah = $gudangSebelum - $qty;
                $rakSesudah = $rakSebelum + $qty;

                DB::update("
                    UPDATE barangs
                    SET stok_gudang = ?, stok_rak = ?, lokasi_rak = ?
                    WHERE id = ?
                ", [$gudangSesudah, $rakSesudah, $lokasiTujuan, $barangId]);

                DB::insert("
                    INSERT INTO mutasi_barang
                    (barang_id, qty_mutasi, lokasi_asal, lokasi_tujuan, catatan)
                    VALUES (?, ?, ?, ?, ?)
                ", [$barangId, $qty, $lokasiAsal, $lokasiTujuan, $catatan ?: null]);

                DB::insert("
                    INSERT INTO transaksis
                    (
                        user_id,
                        jenis,
                        barang_id,
                        jumlah,
                        total_stok_sebelum,
                        total_stok_sesudah,
                        stok_gudang_sebelum,
                        stok_gudang_sesudah,
                        stok_rak_sebelum,
                        stok_rak_sesudah,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (?, 'mutasi', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ", [
                    $userId,
                    $barangId,
                    $qty,
                    $totalSebelum,
                    $totalSebelum,
                    $gudangSebelum,
                    $gudangSesudah,
                    $rakSebelum,
                    $rakSesudah,
                    $catatan ?: "Mutasi dari {$lokasiAsal} ke {$lokasiTujuan}",
                ]);
            });

            $this->saveStockSnapshot();

            return response()->json([
                'success' => true,
                'message' => 'Mutasi barang berhasil',
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function dashboardStock()
    {
        try {
            $row = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM barangs) AS total_barang,
                    (SELECT COALESCE(SUM(stok_gudang + stok_rak), 0) FROM barangs) AS total_stok,
                    (SELECT COUNT(*) FROM barangs WHERE (stok_gudang + stok_rak) < stok_minimum) AS low_stock,
                    (SELECT COUNT(*) FROM transaksis WHERE jenis = 'barang_masuk') AS barang_masuk,
                    (SELECT COUNT(*) FROM transaksis WHERE jenis = 'barang_keluar') AS barang_keluar,
                    (SELECT COUNT(*) FROM transaksis WHERE jenis = 'mutasi') AS mutasi,
                    (SELECT COUNT(*) FROM transaksis WHERE jenis = 'barang_ditolak') AS barang_ditolak
            ");

            return response()->json([
                'success' => true,
                'data' => $row,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function inventoryHistory()
    {
        try {
            $rows = DB::select("
                SELECT
                    transaksis.*,
                    barangs.nama_barang,
                    barangs.kode_barang
                FROM transaksis
                JOIN barangs ON transaksis.barang_id = barangs.id
                ORDER BY transaksis.created_at DESC
            ");

            return response()->json([
                'success' => true,
                'data' => $rows,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function stockLineChart(Request $request)
    {
        try {
            $bulan = (int) $request->query('bulan');
            $minggu = (int) $request->query('minggu');
            $tahun = (int) $request->query('tahun');

            if (!$bulan || !$minggu || !$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap',
                ], 400);
            }

            $weekRanges = [
                1 => [1, 7],
                2 => [8, 14],
                3 => [15, 21],
                4 => [22, 31],
            ];

            [$startDay, $endDay] = $weekRanges[$minggu] ?? $weekRanges[1];

            $rows = DB::select("
                SELECT DATE(created_at) AS tanggal, total_stok, created_at
                FROM stock_snapshots
                WHERE YEAR(created_at) = ?
                AND MONTH(created_at) = ?
                AND DAY(created_at) BETWEEN ? AND ?
                ORDER BY created_at ASC
            ", [$tahun, $bulan, $startDay, $endDay]);

            $latestPerDate = [];

            foreach ($rows as $item) {
                $latestPerDate[$item->tanggal] = $item;
            }

            return response()->json([
                'success' => true,
                'data' => array_values($latestPerDate),
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function notifications()
    {
        try {
            $rows = DB::select("
                SELECT
                    id,
                    title,
                    message,
                    type,
                    pemesanan_id,
                    is_read,
                    created_at
                FROM notifications
                WHERE is_read = 0
                ORDER BY id DESC
                LIMIT 10
            ");

            return response()->json([
                'success' => true,
                'data' => $rows,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }

    public function notificationsCount()
    {
        try {
            $row = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM notifications
                WHERE is_read = 0
            ");

            return response()->json([
                'success' => true,
                'total' => $row->total,
            ]);

        } catch (Exception $err) {
            return $this->errorResponse($err);
        }
    }
}