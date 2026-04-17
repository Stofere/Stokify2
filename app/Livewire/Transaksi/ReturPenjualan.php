<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use App\Models\TransaksiPenjualan;
use App\Models\Produk;
use App\Services\ReturnService;
use Exception;
use Illuminate\Support\Facades\Auth;

class ReturPenjualan extends Component
{
    public $kode_nota = '';
    public $transaksi_aktif = null;
    
    // Form Retur Sementara
    public $items_retur = []; // Keranjang penukaran
    public $catatan = '';

    // State Input per baris penukaran
    public $pilih_detail_id = '';
    public $qty_retur = 1;
    public $kondisi = 'BAGUS';
    public $keyword_pengganti = '';
    public $produk_pengganti_id = '';
    public $produk_pengganti = null;

    public function cariNota()
    {
        $this->transaksi_aktif = TransaksiPenjualan::with(['detailPenjualan.produk'])
            ->where('kode_nota', trim($this->kode_nota))
            ->first();

        if (!$this->transaksi_aktif) {
            $this->addError('pencarian', 'Nota tidak ditemukan!');
            return;
        }

        // Reset state jika nota ganti
        $this->items_retur = [];
        $this->resetInputBaris();
    }

    public function cariProdukPengganti($id)
    {
        $this->produk_pengganti = Produk::find($id);
        $this->produk_pengganti_id = $id;
        $this->keyword_pengganti = '';
    }

    public function tambahItemRetur()
    {
        $this->validate([
            'pilih_detail_id' => 'required',
            'qty_retur' => 'required|numeric|min:0.01',
            'kondisi' => 'required|in:BAGUS,RUSAK',
            'produk_pengganti_id' => 'required',
        ]);

        $detailAwal = $this->transaksi_aktif->detailPenjualan->where('id_detail_penjualan', $this->pilih_detail_id)->first();
        $sisaBisaRetur = $detailAwal->jumlah - $detailAwal->jumlah_diretur;

        if ($this->qty_retur > $sisaBisaRetur) {
            $this->addError('form_retur', "Qty retur melebihi batas! Maksimal: $sisaBisaRetur " . $detailAwal->satuan_saat_jual);
            return;
        }

        $hargaLama = $detailAwal->harga_satuan * $this->qty_retur;
        $hargaBaru = $this->produk_pengganti->harga_jual_satuan * $this->qty_retur;
        $subtotalSelisih = $hargaBaru - $hargaLama;

        $this->items_retur[] = [
            'id_detail_penjualan' => $this->pilih_detail_id,
            'nama_produk_lama' => $detailAwal->produk->nama_produk,
            'id_produk_pengganti' => $this->produk_pengganti_id,
            'nama_produk_pengganti' => $this->produk_pengganti->nama_produk,
            'jumlah' => $this->qty_retur,
            'kondisi_barang_dikembalikan' => $this->kondisi,
            'selisih_biaya' => $subtotalSelisih,
        ];

        $this->resetInputBaris();
    }

    public function hapusItemRetur($index)
    {
        unset($this->items_retur[$index]);
        $this->items_retur = array_values($this->items_retur);
    }

    private function resetInputBaris()
    {
        $this->pilih_detail_id = '';
        $this->qty_retur = 1;
        $this->kondisi = 'BAGUS';
        $this->produk_pengganti_id = '';
        $this->produk_pengganti = null;
        $this->keyword_pengganti = '';
    }

    public function prosesRetur(ReturnService $returnService)
    {
        if (empty($this->items_retur)) {
            $this->addError('sistem', 'Keranjang retur masih kosong!');
            return;
        }

        try {
            $notaRetur = $returnService->prosesRetur(
                $this->transaksi_aktif->id_transaksi_penjualan,
                Auth::id(),
                $this->items_retur,
                $this->catatan
            );

            session()->flash('sukses', "Retur berhasil diproses! Kode Retur: " . $notaRetur->kode_retur);
            
            // Reset Total
            $this->transaksi_aktif = null;
            $this->kode_nota = '';
            $this->items_retur = [];
            $this->catatan = '';

        } catch (Exception $e) {
            $this->addError('sistem', $e->getMessage());
        }
    }

    public function render()
    {
        $daftarPencarianPengganti = collect();
        if (strlen($this->keyword_pengganti) >= 2) {
            $daftarPencarianPengganti = Produk::where('status_aktif', true)
                ->whereRaw("MATCH(index_pencarian) AGAINST(? IN BOOLEAN MODE)", [$this->keyword_pengganti . '*'])
                ->limit(5)->get();
        }

        return view('livewire.transaksi.retur-penjualan', [
            'daftarPencarianPengganti' => $daftarPencarianPengganti
        ]);
    }
}