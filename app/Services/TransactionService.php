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
                'tanggal_transaksi' => $dataNota['tanggal_transaksi'] ?? now(),
                'catatan' => $dataNota['catatan'] ?? null,
            ]);

            // 2. Proses tiap item di keranjang
            foreach ($keranjang as $item) {
                $produk = Produk::lockForUpdate()->find($item['id_produk']);

                if (!$produk) {
                    throw new Exception("Produk tidak ditemukan di database.");
                }

                // Cek stok berdasarkan berat fisik timbangan (potong_gudang)
                if ($produk->lacak_stok && $produk->stok_saat_ini < $item['jumlah_potong_gudang']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi. Sisa: {$produk->stok_saat_ini}");
                }

                $subtotal = $item['subtotal']; // Sudah dihitung di Livewire
                $totalHarga += $subtotal;

                $stokSebelum = $produk->stok_saat_ini;
                $stokSesudah = $stokSebelum - $item['jumlah_potong_gudang'];

                if ($produk->lacak_stok) {
                    $produk->update(['stok_saat_ini' => $stokSesudah]);
                }

                // LOGIKA CERDAS UNTUK NOTA:
                // Jika jual eceran (Meter), satuannya ditulis "Meter", tapi harganya harga meter.
                // Jika jual utama (KG), satuannya ditulis "KG".
                $satuanNota = $item['tipe_jual'] === 'eceran' ? 'meter' : $produk->satuan;
                
                DetailPenjualan::create([
                    'id_transaksi_penjualan' => $transaksi->id_transaksi_penjualan,
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $item['jumlah_jual'], // Angka yang tampil di nota
                    'jumlah_potong_gudang' => $item['jumlah_potong_gudang'], // KG fisik yang dipotong dari gudang
                    'satuan_saat_jual' => $satuanNota,
                    'harga_satuan' => $item['harga_terpakai'],
                    'subtotal' => $subtotal,
                ]);

                // RIWAYAT STOK (CCTV):
                // Mencatat keterangan jumlah terjual untuk semua tipe jual (Meter maupun KG/satuan utama)
                $satuanLabel = $item['tipe_jual'] === 'eceran' ? 'Meter' : strtoupper($produk->satuan);
                $ketTambahan = " (Terjual {$item['jumlah_jual']} {$satuanLabel})";
                
                RiwayatStok::create([
                    'id_produk' => $produk->id_produk,
                    'user_id' => $userId,
                    'id_transaksi_penjualan' => $transaksi->id_transaksi_penjualan,
                    'tipe' => 'KELUAR',
                    'jumlah' => $item['jumlah_potong_gudang'], // Memotong fisik aktual
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan' => "Penjualan No: " . $transaksi->kode_nota . $ketTambahan,
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
        $prefix = "Nota_Jual_{$tanggal}-";

        // Cari nomor urut TERTINGGI dari kode_nota yang memiliki prefix tanggal hari ini.
        // Ini lebih akurat daripada count() karena:
        // 1. Tidak terpengaruh oleh tanggal_transaksi (yang bisa di-backdate).
        // 2. Tetap aman meskipun ada nota yang dihapus (tidak akan terjadi tabrakan).
        $lastNota = TransaksiPenjualan::where('kode_nota', 'LIKE', $prefix . '%')
            ->selectRaw("MAX(CAST(SUBSTRING(kode_nota, ?, 3) AS UNSIGNED)) as max_urut", [strlen($prefix) + 1])
            ->value('max_urut');

        $urutan = str_pad(($lastNota ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$urutan}";
    }
}