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
}