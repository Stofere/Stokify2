<?php

namespace App\Livewire\Master;

use Livewire\Component;
use App\Models\Produk;
use App\Services\StockService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PenyesuaianStok extends Component
{
    public $keyword = '';
    public $id_produk = null;
    public $produk_terpilih = null;

    // Form
    public $tipe_penyesuaian = 'KOREKSI_MINUS';
    public $jumlah = 0;
    public $keterangan = '';
    public $password_admin = '';

    public function pilihProduk($id)
    {
        $this->produk_terpilih = Produk::find($id);
        $this->id_produk = $id;
        $this->keyword = ''; // Tutup dropdown pencarian
    }

    public function simpan(StockService $stockService)
    {
        // 1. Validasi Input Dasar
        $this->validate([
            'id_produk' => 'required',
            'tipe_penyesuaian' => 'required|in:KOREKSI_PLUS,KOREKSI_MINUS',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'required|string|min:5',
            'password_admin' => 'required',
        ]);

        // 2. Keamanan Tingkat Tinggi: Validasi Password Admin yang sedang login (Sesuai PRD Note)
        if (!Hash::check($this->password_admin, Auth::user()->password)) {
            $this->addError('password_admin', 'Password otorisasi salah! Tindakan dibatalkan.');
            return;
        }

        // 3. Lempar ke Service Layer untuk eksekusi
        try {
            $stockService->adjustStokManual(
                $this->id_produk,
                Auth::id(),
                $this->tipe_penyesuaian,
                $this->jumlah,
                $this->keterangan
            );

            session()->flash('sukses', "Stok fisik barang berhasil diperbarui dan dicatat dalam audit.");
            
            // Reset form
            $this->reset(['id_produk', 'produk_terpilih', 'tipe_penyesuaian', 'jumlah', 'keterangan', 'password_admin']);

        } catch (Exception $e) {
            $this->addError('sistem', $e->getMessage());
        }
    }

    public function render()
    {
        $hasilPencarian = collect();
        if (strlen($this->keyword) >= 2) {
            $hasilPencarian = Produk::where('lacak_stok', true)
                ->whereRaw("MATCH(index_pencarian) AGAINST(? IN BOOLEAN MODE)", [$this->keyword . '*'])
                ->limit(5)
                ->get();
        }

        return view('livewire.master.penyesuaian-stok', [
            'daftarPencarian' => $hasilPencarian
        ]);
    }
}