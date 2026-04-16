<?php

namespace App\Services;

use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\TransaksiRetur;
use App\Models\DetailRetur;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;
use Exception;

class ReturnService
{
    /**
     * Memproses Retur/Tukar Barang.
     * $itemsRetur format: 
     * [['id_detail_penjualan' => x, 'id_produk_pengganti' => y, 'jumlah' => z, 'kondisi_barang_dikembalikan' => 'BAGUS'/'RUSAK']]
     */
    public function prosesRetur(int $idTransaksiPenjualan, int $userId, array $itemsRetur, string $catatan = null)
    {
        if (empty($itemsRetur)) {
            throw new Exception("Daftar barang yang diretur tidak boleh kosong.");
        }

        return DB::transaction(function () use ($idTransaksiPenjualan, $userId, $itemsRetur, $catatan) {
            
            // Kunci Transaksi Induk
            $transaksiAwal = TransaksiPenjualan::lockForUpdate()->find($idTransaksiPenjualan);
            if (!$transaksiAwal) {
                throw new Exception("Nota penjualan asli tidak ditemukan.");
            }

            $totalBiayaRetur = 0; // Nilai (+) berarti pelanggan nombok, Nilai (-) berarti toko kembalikan uang

            // 1. Buat Sampul Nota Retur
            $transaksiRetur = TransaksiRetur::create([
                'kode_retur' => $this->generateKodeRetur(),
                'id_transaksi_penjualan' => $transaksiAwal->id_transaksi_penjualan,
                'user_id' => $userId,
                'tanggal_retur' => now(),
                'total_biaya_retur' => 0, // Diupdate di akhir
                'catatan' => $catatan,
            ]);

            // 2. Proses tiap item yang ditukar
            foreach ($itemsRetur as $item) {
                $qtyRetur = $item['jumlah'];
                
                // Kunci baris detail nota
                $detailAwal = DetailPenjualan::lockForUpdate()->find($item['id_detail_penjualan']);
                if (!$detailAwal || $detailAwal->id_transaksi_penjualan != $transaksiAwal->id_transaksi_penjualan) {
                    throw new Exception("Item detail tidak valid untuk nota ini.");
                }

                // VALIDASI MAX RETUR (Mencegah pelanggan meretur lebih dari yang dibeli)
                $sisaBisaDiretur = $detailAwal->jumlah - $detailAwal->jumlah_diretur;
                if ($qtyRetur > $sisaBisaDiretur) {
                    throw new Exception("Jumlah retur melebihi batas. Sisa yang boleh diretur: " . $sisaBisaDiretur);
                }

                // Kunci produk lama dan produk baru
                $produkLama = Produk::lockForUpdate()->find($detailAwal->id_produk);
                $produkPengganti = Produk::lockForUpdate()->find($item['id_produk_pengganti']);

                if (!$produkLama || !$produkPengganti) {
                    throw new Exception("Data produk fisik (lama/pengganti) tidak ditemukan.");
                }

                // Pastikan stok produk pengganti cukup
                if ($produkPengganti->lacak_stok && $produkPengganti->stok_saat_ini < $qtyRetur) {
                    throw new Exception("Stok produk pengganti ({$produkPengganti->nama_produk}) tidak mencukupi.");
                }

                // --- KALKULASI UANG ---
                // Misal: Harga Lama 100k, Harga Baru 150k. Selisih = 50k (Nombok)
                $hargaTotalLama = $detailAwal->harga_satuan * $qtyRetur;
                $hargaTotalBaru = $produkPengganti->harga_jual_satuan * $qtyRetur;
                $subtotalBiaya = $hargaTotalBaru - $hargaTotalLama; 
                $totalBiayaRetur += $subtotalBiaya;

                // --- MANAJEMEN STOK PRODUK LAMA (DIKEMBALIKAN) ---
                // Jika Bagus, stok gudang bertambah. Jika Rusak, stok hangus (dibuang/masuk ruang cacat).
                if ($item['kondisi_barang_dikembalikan'] === 'BAGUS' && $produkLama->lacak_stok) {
                    $stokSebelumLama = $produkLama->stok_saat_ini;
                    $stokSesudahLama = $stokSebelumLama + $qtyRetur;
                    
                    $produkLama->update(['stok_saat_ini' => $stokSesudahLama]);

                    RiwayatStok::create([
                        'id_produk' => $produkLama->id_produk,
                        'user_id' => $userId,
                        'id_retur' => $transaksiRetur->id_retur,
                        'tipe' => 'MASUK', // Barang masuk ke gudang karena retur
                        'jumlah' => $qtyRetur,
                        'stok_sebelum' => $stokSebelumLama,
                        'stok_sesudah' => $stokSesudahLama,
                        'keterangan' => "Pengembalian Retur (Kondisi Bagus). Nota: " . $transaksiAwal->kode_nota,
                    ]);
                }

                // --- MANAJEMEN STOK PRODUK PENGGANTI (DIKELUARKAN) ---
                if ($produkPengganti->lacak_stok) {
                    $stokSebelumBaru = $produkPengganti->stok_saat_ini;
                    $stokSesudahBaru = $stokSebelumBaru - $qtyRetur;

                    $produkPengganti->update(['stok_saat_ini' => $stokSesudahBaru]);

                    RiwayatStok::create([
                        'id_produk' => $produkPengganti->id_produk,
                        'user_id' => $userId,
                        'id_retur' => $transaksiRetur->id_retur,
                        'tipe' => 'KELUAR', // Barang pengganti keluar gudang
                        'jumlah' => $qtyRetur,
                        'stok_sebelum' => $stokSebelumBaru,
                        'stok_sesudah' => $stokSesudahBaru,
                        'keterangan' => "Pengganti Retur. Nota Asli: " . $transaksiAwal->kode_nota,
                    ]);
                }

                // --- UPDATE CATATAN NOTA & RETUR ---
                // Tambahkan 'jumlah_diretur' ke nota asli sebagai segel pengaman
                $detailAwal->update(['jumlah_diretur' => $detailAwal->jumlah_diretur + $qtyRetur]);

                DetailRetur::create([
                    'id_retur' => $transaksiRetur->id_retur,
                    'id_produk_dikembalikan' => $produkLama->id_produk,
                    'id_produk_pengganti' => $produkPengganti->id_produk,
                    'jumlah' => $qtyRetur,
                    'kondisi_barang_dikembalikan' => $item['kondisi_barang_dikembalikan'],
                    'subtotal_biaya' => $subtotalBiaya,
                ]);
            }

            // Update Total Biaya Selisih Retur & Status Penjualan Asli
            $transaksiRetur->update(['total_biaya_retur' => $totalBiayaRetur]);
            $transaksiAwal->update(['status_penjualan' => 'DIRETUR']);

            return $transaksiRetur;
        });
    }

    private function generateKodeRetur(): string
    {
        $tanggal = now()->format('d-m-Y');
        $lastRetur = TransaksiRetur::whereDate('tanggal_retur', now()->toDateString())->count();
        $urutan = str_pad($lastRetur + 1, 3, '0', STR_PAD_LEFT);
        
        return "Retur_{$tanggal}-{$urutan}";
    }
}