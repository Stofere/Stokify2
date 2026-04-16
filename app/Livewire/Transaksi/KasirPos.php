<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Marketing;
use App\Services\TransactionService;
use Exception;
use Illuminate\Support\Facades\Auth;

class KasirPos extends Component
{
    // Form & Filter
    public $keyword = '';
    
    // Data Master
    public $daftarPelanggan = [];
    public $daftarMarketing = [];

    // State Transaksi
    public $id_pelanggan = null;
    public $id_marketing = null;
    public $catatan = '';
    
    // State Keranjang
    public $keranjang = []; // Format: [['id_produk', 'nama_produk', 'satuan', 'jumlah', 'harga_satuan', 'subtotal', 'max_stok', 'lacak_stok']]
    public $total_belanja = 0;

    public function mount()
    {
        $this->daftarPelanggan = Pelanggan::where('aktif', true)->get();
        $this->daftarMarketing = Marketing::where('aktif', true)->get();
    }

    public function tambahKeKeranjang(int $idProduk)
    {
        $produk = Produk::find($idProduk);

        if (!$produk || !$produk->status_aktif) {
            $this->addError('keranjang', 'Produk tidak valid.');
            return;
        }

        // Cek apakah produk sudah ada di keranjang
        $index = collect($this->keranjang)->search(fn($item) => $item['id_produk'] === $idProduk);

        if ($index !== false) {
            // Jika sudah ada, tambah jumlahnya
            $qtyBaru = $this->keranjang[$index]['jumlah'] + 1;
            
            // Validasi stok instan di layar
            if ($produk->lacak_stok && $qtyBaru > $produk->stok_saat_ini) {
                session()->flash('error', "Stok {$produk->nama_produk} tidak mencukupi!");
                return;
            }

            $this->keranjang[$index]['jumlah'] = $qtyBaru;
            $this->keranjang[$index]['subtotal'] = $qtyBaru * $this->keranjang[$index]['harga_satuan'];
        } else {
            // Jika belum ada, masukkan ke keranjang
            if ($produk->lacak_stok && $produk->stok_saat_ini < 1) {
                session()->flash('error', "Stok {$produk->nama_produk} habis!");
                return;
            }

            $this->keranjang[] = [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'satuan' => $produk->satuan,
                'jumlah' => 1,
                'harga_satuan' => $produk->harga_jual_satuan,
                'subtotal' => $produk->harga_jual_satuan,
                'max_stok' => $produk->stok_saat_ini,
                'lacak_stok' => $produk->lacak_stok,
            ];
        }

        $this->hitungTotal();
    }

    public function hapusItem(int $index)
    {
        unset($this->keranjang[$index]);
        $this->keranjang = array_values($this->keranjang); // Re-index array
        $this->hitungTotal();
    }

    public function hitungTotal()
    {
        $this->total_belanja = collect($this->keranjang)->sum('subtotal');
    }

    public function prosesPembayaran(TransactionService $transactionService)
    {
        // 1. Validasi Input (Thin Component Rule)
        if (empty($this->keranjang)) {
            $this->addError('keranjang', 'Keranjang belanja masih kosong.');
            return;
        }

        // 2. Lempar ke Service Layer (Fat Service Rule)
        try {
            $dataNota = [
                'id_pelanggan' => $this->id_pelanggan,
                'id_marketing' => $this->id_marketing,
                'catatan' => $this->catatan,
            ];

            // Panggil service yang berisi DB Transaction dan Pessimistic Locking
            $nota = $transactionService->createPenjualan(Auth::id(), $dataNota, $this->keranjang);

            // 3. Jika berhasil, reset form dan tampilkan pesan sukses
            session()->flash('sukses', "Transaksi berhasil! Nomor Nota: " . $nota->kode_nota);
            $this->reset(['keranjang', 'total_belanja', 'id_pelanggan', 'id_marketing', 'catatan', 'keyword']);
            
        } catch (Exception $e) {
            // Tangkap pesan error dari logic Service (Contoh: "Stok kurang")
            $this->addError('checkout', $e->getMessage());
        }
    }

    public function render()
    {
        // Fitur NO-LAG FULLTEXT SEARCH (Mencari SKU, Nama, dan JSON Attribute sekaligus)
        $hasilPencarian = collect();
        
        if (strlen($this->keyword) >= 2) {
            $hasilPencarian = Produk::where('status_aktif', true)
                ->whereRaw("MATCH(index_pencarian) AGAINST(? IN BOOLEAN MODE)", [$this->keyword . '*'])
                ->limit(20)
                ->get();
        } elseif (empty($this->keyword)) {
            // Default tampilkan 10 produk terbaru
            $hasilPencarian = Produk::where('status_aktif', true)->latest()->limit(10)->get();
        }

        return view('livewire.transaksi.kasir-pos', [
            'daftarProduk' => $hasilPencarian
        ]);
    }
}