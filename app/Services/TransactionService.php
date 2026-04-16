<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Memproses Checkout POS.
     * $dataNota berisi: id_pelanggan, id_marketing, catatan.
     * $keranjang format: [['id_produk' => x, 'jumlah' => y, 'harga_satuan' => z, 'subtotal' => a]]
     */
    public function createPenjualan(int $userId, array $dataNota, array $keranjang)
    {
        if (empty($keranjang)) {
            throw new Exception("Keranjang belanja kosong.");
        }

        return DB::transaction(function () use ($userId, $dataNota, $keranjang) {
            $totalHarga = 0;
            $detailBeli = [];

            // 1. Buat Header Sampul Nota
            $transaksi = TransaksiPenjualan::create([
                'kode_nota' => $this->generateKodeNota(),
                'user_id' => $userId,
                'id_pelanggan' => $dataNota['id_pelanggan'] ?? null,
                'id_marketing' => $dataNota['id_marketing'] ?? null,
                'total_harga' => 0, // Akan diupdate di akhir
                'status_penjualan' => 'SELESAI',
                'tanggal_transaksi' => now(),
                'catatan' => $dataNota['catatan'] ?? null,
            ]);

            // 2. Proses tiap item di keranjang
            foreach ($keranjang as $item) {
                // KUNCI PENTING: Cegah Race Condition 2 Kasir
                $produk = Produk::lockForUpdate()->find($item['id_produk']);

                if (!$produk) {
                    throw new Exception("Produk tidak ditemukan di database.");
                }

                // Cek stok khusus barang yang di-track stoknya
                if ($produk->lacak_stok && $produk->stok_saat_ini < $item['jumlah']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi. Sisa: {$produk->stok_saat_ini}");
                }

                $subtotal = $item['jumlah'] * $item['harga_satuan'];
                $totalHarga += $subtotal;

                $stokSebelum = $produk->stok_saat_ini;
                $stokSesudah = $stokSebelum - $item['jumlah'];

                // Kurangi stok jika produk dilacak
                if ($produk->lacak_stok) {
                    $produk->update(['stok_saat_ini' => $stokSesudah]);
                }

                // Simpan baris detail nota
                DetailPenjualan::create([
                    'id_transaksi_penjualan' => $transaksi->id_transaksi_penjualan,
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $item['jumlah'],
                    'satuan_saat_jual' => $produk->satuan,
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                ]);

                // Catat mutasi stok keluar
                RiwayatStok::create([
                    'id_produk' => $produk->id_produk,
                    'user_id' => $userId,
                    'id_transaksi_penjualan' => $transaksi->id_transaksi_penjualan,
                    'tipe' => 'KELUAR',
                    'jumlah' => $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan' => "Penjualan No: " . $transaksi->kode_nota,
                ]);
            }

            // Update Total Harga di Sampul Nota
            $transaksi->update(['total_harga' => $totalHarga]);

            return $transaksi;
        });
    }

    private function generateKodeNota(): string
    {
        $tanggal = now()->format('d-m-Y');
        // Cari nota terakhir di hari yang sama untuk auto-increment harian
        $lastNota = TransaksiPenjualan::whereDate('tanggal_transaksi', now()->toDateString())->count();
        $urutan = str_pad($lastNota + 1, 3, '0', STR_PAD_LEFT);
        
        return "Nota_Jual_{$tanggal}-{$urutan}";
    }
}