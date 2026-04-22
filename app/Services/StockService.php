<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * Memproses penyesuaian stok gudang secara manual.
     * Catatan: Validasi password admin harus dilakukan di Livewire Controller sebelum fungsi ini dipanggil.
     */
    public function adjustStokManual(int $idProduk, int $userId, string $tipe, float $jumlah, string $keterangan)
    {
        if ($jumlah <= 0) {
            throw new Exception("Jumlah penyesuaian stok harus lebih dari 0.");
        }

        if (!in_array($tipe, ['KOREKSI_PLUS', 'KOREKSI_MINUS'])) {
            throw new Exception("Tipe penyesuaian tidak valid.");
        }

        return DB::transaction(function () use ($idProduk, $userId, $tipe, $jumlah, $keterangan) {
            // 1. Kunci baris produk ini agar tidak bisa dibeli/diubah kasir lain saat proses berlangsung
            $produk = Produk::lockForUpdate()->find($idProduk);

            if (!$produk) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $stokSebelum = $produk->stok_saat_ini;
            $stokSesudah = ($tipe === 'KOREKSI_PLUS') 
                            ? $stokSebelum + $jumlah 
                            : $stokSebelum - $jumlah;

            // Jika KOREKSI_MINUS, pastikan stok tidak menjadi negatif
            if ($tipe === 'KOREKSI_MINUS' && $stokSesudah < 0) {
                throw new Exception("Stok tidak mencukupi untuk dikurangi. Sisa stok saat ini: " . $stokSebelum);
            }

            // 2. Update stok produk fisik
            $produk->update(['stok_saat_ini' => $stokSesudah]);

            // 3. Catat jejak audit (CCTV Gudang Anti-Maling)
            RiwayatStok::create([
                'id_produk' => $idProduk,
                'user_id' => $userId,
                'tipe' => $tipe,
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => $keterangan,
            ]);

            return $produk;
        });
    }

    /**
     * Memproses penyesuaian ROL fisik secara manual untuk kategori kabel.
     */
    public function adjustRolManual(int $idProduk, int $userId, string $tipe, int $jumlah, string $keterangan)
    {
        if ($jumlah <= 0) {
            throw new Exception("Jumlah penyesuaian rol harus lebih dari 0.");
        }

        if (!in_array($tipe, ['ROL_MASUK', 'ROL_KELUAR'])) {
            throw new Exception("Tipe penyesuaian rol tidak valid.");
        }

        return DB::transaction(function () use ($idProduk, $userId, $tipe, $jumlah, $keterangan) {
            $produk = Produk::lockForUpdate()->find($idProduk);

            if (!$produk) {
                throw new Exception("Produk tidak ditemukan.");
            }

            if (!$produk->kategori->lacak_rol) {
                throw new Exception("Kategori produk ini tidak melacak rol.");
            }

            $rolSebelum = $produk->stok_rol;
            $rolSesudah = ($tipe === 'ROL_MASUK') 
                            ? $rolSebelum + $jumlah 
                            : $rolSebelum - $jumlah;

            if ($tipe === 'ROL_KELUAR' && $rolSesudah < 0) {
                throw new Exception("Stok rol tidak mencukupi untuk dikurangi. Sisa rol saat ini: " . $rolSebelum);
            }

            $produk->update(['stok_rol' => $rolSesudah]);

            RiwayatStok::create([
                'id_produk' => $idProduk,
                'user_id' => $userId,
                'tipe' => $tipe,
                'jumlah' => 0, // Jumlah KG mutasi = 0 untuk mutasi rol murni
                'stok_sebelum' => $produk->stok_saat_ini, // KG tidak berubah
                'stok_sesudah' => $produk->stok_saat_ini, // KG tidak berubah
                'rol_mutasi' => $jumlah,
                'rol_sebelum' => $rolSebelum,
                'rol_sesudah' => $rolSesudah,
                'keterangan' => $keterangan,
            ]);

            return $produk;
        });
    }
}