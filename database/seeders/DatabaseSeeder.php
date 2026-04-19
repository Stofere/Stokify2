<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Atribut;
use App\Models\Produk;
use App\Models\Marketing;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Akun Pengguna (Admin & Owner)
        $owner = User::create([
            'name' => 'Yohanes',
            'username' => 'owner',
            'password' => Hash::make('password123'),
            'peran' => 'OWNER',
        ]);

        $admin = User::create([
            'name' => 'Yesaya',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'peran' => 'ADMIN',
        ]);

        // 2. Setup Master Pelaku
        Marketing::create(['nama' => 'Simeon', 'telepon' => '081234567890']);
        Marketing::create(['nama' => 'Noval', 'telepon' => '081987654321']);
        
        Pelanggan::create(['nama' => 'Dion Jastro', 'alamat' => 'Jl. Merdeka No. 1']);
        Pelanggan::create(['nama' => 'Kusmanto']);

        // // 3. Setup Master Atribut (JSON Rulebook sesuai BRD)
        // $attrMerk = Atribut::create(['nama_atribut' => 'Merk', 'pilihan_opsi' => ['CN', 'AX', 'FAB', 'PD']]);
        // $attrRing = Atribut::create(['nama_atribut' => 'Ring', 'pilihan_opsi' => ['2', '3', '4']]);
        // $attrMotif = Atribut::create(['nama_atribut' => 'Motif', 'pilihan_opsi' => ['Polos', 'Garis']]);
        // $attrTekstur = Atribut::create(['nama_atribut' => 'Tekstur', 'pilihan_opsi' => ['CT-Coating', 'JRK-Jeruk', 'UK-Ring Kualitas Tebal', 'JHT-Jahit']]);
        // $attrMika = Atribut::create(['nama_atribut' => 'Mika', 'pilihan_opsi' => ['Mika']]);

        // // 4. Setup Master Kategori & Pasangkan dengan Atribut (Pivot)
        // $katFullring = Kategori::create(['nama_kategori' => 'Fullring']);
        // $katFullring->atribut()->attach([$attrMerk->id_atribut, $attrRing->id_atribut, $attrMotif->id_atribut, $attrTekstur->id_atribut]);

        // $katWoofer = Kategori::create(['nama_kategori' => 'Woofer']);
        // $katWoofer->atribut()->attach([$attrMerk->id_atribut, $attrMotif->id_atribut, $attrTekstur->id_atribut, $attrMika->id_atribut]);

        // $katKabel = Kategori::create(['nama_kategori' => 'Kabel']);
        // // Kabel tidak punya atribut tambahan (Sesuai BRD)

        // $katRing = Kategori::create(['nama_kategori' => 'Ring']);
        // $katRing->atribut()->attach([$attrRing->id_atribut]);

        // $katLem = Kategori::create(['nama_kategori' => 'Lem']);
        // // Lem tidak punya atribut tambahan

        // // 5. Setup Master Produk (Katalog Awal)
        // $produkData = [
        //     [
        //         'id_kategori' => $katFullring->id_kategori,
        //         'kode_barang' => 'FR-12-CN-GRS-3',
        //         'nama_produk' => 'Fr 12" CN GRS (3)',
        //         'satuan' => 'pcs',
        //         'harga_jual_satuan' => 150000,
        //         'stok_saat_ini' => 50,
        //         'metadata' => ['Merk' => 'CN', 'Motif' => 'Garis', 'Ring' => '3'],
        //     ],
        //     [
        //         'id_kategori' => $katFullring->id_kategori,
        //         'kode_barang' => 'FR-12-CN-PLS-CT-2',
        //         'nama_produk' => 'Fr 12" CN PLS CT (2)',
        //         'satuan' => 'pcs',
        //         'harga_jual_satuan' => 145000,
        //         'stok_saat_ini' => 30,
        //         'metadata' => ['Merk' => 'CN', 'Motif' => 'Polos', 'Tekstur' => 'CT-Coating', 'Ring' => '2'],
        //     ],
        //     [
        //         'id_kategori' => $katWoofer->id_kategori,
        //         'kode_barang' => 'WOF-10-MIKA',
        //         'nama_produk' => 'Wof 10" Lb36 Mika T5,4',
        //         'satuan' => 'pcs',
        //         'harga_jual_satuan' => 200000,
        //         'stok_saat_ini' => 25,
        //         'metadata' => ['Mika' => 'Mika'],
        //     ],
        //     // Contoh Implementasi SKU Kabel (Multi-Satuan) sesuai BRD
        //     [
        //         'id_kategori' => $katKabel->id_kategori,
        //         'kode_barang' => 'KBL-20-EMAS-METER',
        //         'nama_produk' => 'Kbl No. 20 Emas (Meter)',
        //         'satuan' => 'meter',
        //         'harga_jual_satuan' => 15000.50, // Harga eceran
        //         'stok_saat_ini' => 100.5, // 100.5 meter
        //         'metadata' => null,
        //     ],
        //     [
        //         'id_kategori' => $katKabel->id_kategori,
        //         'kode_barang' => 'KBL-20-EMAS-KG',
        //         'nama_produk' => 'Kbl No. 20 Emas (KG)',
        //         'satuan' => 'kg',
        //         'harga_jual_satuan' => 120000, // Harga grosir per KG
        //         'stok_saat_ini' => 10, // 10 KG
        //         'metadata' => null,
        //     ],
        //     [
        //         'id_kategori' => $katLem->id_kategori,
        //         'kode_barang' => 'LEM-ARALDIT',
        //         'nama_produk' => 'Lem Araldit',
        //         'satuan' => 'pcs',
        //         'harga_jual_satuan' => 25000,
        //         'stok_saat_ini' => 100,
        //         'metadata' => null,
        //     ],
        // ];

        // foreach ($produkData as $data) {
        //     // Gabungkan text untuk algoritma No-Lag Fulltext Search
        //     $metaString = $data['metadata'] ? implode(' ', array_values($data['metadata'])) : '';
        //     $data['index_pencarian'] = strtolower($data['kode_barang'] . ' ' . $data['nama_produk'] . ' ' . $metaString);
            
        //     $produk = Produk::create($data);

        //     // 6. Catat saldo awal ke Riwayat Stok (Sistem Anti-Maling)
        //     \App\Models\RiwayatStok::create([
        //         'id_produk' => $produk->id_produk,
        //         'user_id' => $owner->id,
        //         'tipe' => 'AWAL',
        //         'jumlah' => $produk->stok_saat_ini,
        //         'stok_sebelum' => 0,
        //         'stok_sesudah' => $produk->stok_saat_ini,
        //         'keterangan' => 'Saldo awal sistem',
        //     ]);
        // }
    }
}