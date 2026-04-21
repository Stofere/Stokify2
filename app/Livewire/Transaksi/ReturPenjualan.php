<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Marketing;
use App\Services\ReturnService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ReturPenjualan extends Component
{
    // State Filter Nota
    public $filter_tanggal_mulai;
    public $filter_tanggal_akhir;
    public $filter_keyword = '';
    public $filter_pelanggan_id = '';
    public $filter_marketing_id = '';
    
    // Data Dropdown Filter
    public $daftarPelanggan = [];
    public $daftarMarketing = [];

    // State Tampilan Nota
    public $notaTerpilih = null;

    // State Modal Retur
    public $showReturModal = false;
    public $detailTerpilih = null;
    public $qty_retur = 1;
    public $kondisi_retur = 'BAGUS';
    
    // State Pencarian Barang Pengganti
    public $search_produk_pengganti = '';
    public $produk_pengganti = null;
    
    // State Form Eksekusi
    public $catatan = '';
    public $password_admin = '';

    public function mount()
    {
        $this->filter_tanggal_mulai = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->filter_tanggal_akhir = Carbon::now()->format('Y-m-d');
        
        $this->daftarPelanggan = Pelanggan::where('aktif', true)->orderBy('nama')->get();
        $this->daftarMarketing = Marketing::where('aktif', true)->orderBy('nama')->get();
    }

    public function pilihNota($id_transaksi)
    {
        $this->notaTerpilih = TransaksiPenjualan::with(['detailPenjualan.produk', 'user', 'pelanggan'])
            ->find($id_transaksi);
    }

    public function batalPilihNota()
    {
        $this->notaTerpilih = null;
        $this->tutupModalRetur();
    }

    public function bukaModalRetur($id_detail)
    {
        $this->detailTerpilih = DetailPenjualan::with('produk')->find($id_detail);
        $this->produk_pengganti = $this->detailTerpilih->produk; 
        
        $this->qty_retur = 1;
        $this->kondisi_retur = 'BAGUS';
        $this->search_produk_pengganti = '';
        $this->catatan = '';
        $this->password_admin = '';
        
        $this->showReturModal = true;
    }

    public function tutupModalRetur()
    {
        $this->showReturModal = false;
        $this->detailTerpilih = null;
        $this->produk_pengganti = null;
    }

    public function pilihBarangPengganti($id_produk)
    {
        $this->produk_pengganti = Produk::find($id_produk);
        $this->search_produk_pengganti = ''; 
    }

    public function prosesRetur(ReturnService $returnService)
    {
        $sisaMaksimal = $this->detailTerpilih->jumlah - $this->detailTerpilih->jumlah_diretur;

        $this->validate([
            'qty_retur' => "required|numeric|min:0.01|max:$sisaMaksimal",
            'kondisi_retur' => 'required|in:BAGUS,RUSAK',
            'catatan' => 'required|string|min:3',
            'password_admin' => 'required',
        ]);

        // BACKEND DOUBLE CHECK: Cegah Retur Desimal di satuan PCS
        $satuan = strtolower($this->detailTerpilih->produk->satuan);
        if (in_array($satuan, ['pcs', 'biji', 'unit', 'buah']) && fmod($this->qty_retur, 1) !== 0.0) {
            $this->addError('qty_retur', "Barang dengan satuan {$satuan} tidak boleh diretur dengan nilai koma (desimal)!");
            return;
        }

        if (!$this->produk_pengganti) {
            $this->addError('search_produk_pengganti', 'Pilih barang pengganti terlebih dahulu!');
            return;
        }

        if (!Hash::check($this->password_admin, Auth::user()->password)) {
            $this->addError('password_admin', 'Password otorisasi salah!');
            return;
        }

        $itemsRetur = [
            [
                'id_detail_penjualan' => $this->detailTerpilih->id_detail_penjualan,
                'id_produk_pengganti' => $this->produk_pengganti->id_produk,
                'jumlah' => $this->qty_retur,
                'kondisi_barang_dikembalikan' => $this->kondisi_retur,
            ]
        ];

        try {
            $returnService->prosesRetur(
                $this->notaTerpilih->id_transaksi_penjualan,
                Auth::id(),
                $itemsRetur,
                $this->catatan
            );

            session()->flash('sukses', 'Proses Retur Berhasil! Mutasi stok & uang telah disesuaikan.');
            $this->notaTerpilih->refresh(); 
            $this->tutupModalRetur();

        } catch (Exception $e) {
            $this->addError('password_admin', $e->getMessage()); 
        }
    }

    public function render()
    {
        $daftar_nota = collect();
        if (!$this->notaTerpilih) {
            $queryNota = TransaksiPenjualan::with(['pelanggan', 'marketing'])
                ->whereBetween('tanggal_transaksi', [
                    $this->filter_tanggal_mulai . ' 00:00:00',
                    $this->filter_tanggal_akhir . ' 23:59:59'
                ]);

            if ($this->filter_pelanggan_id) {
                $queryNota->where('id_pelanggan', $this->filter_pelanggan_id);
            }

            if ($this->filter_marketing_id) {
                $queryNota->where('id_marketing', $this->filter_marketing_id);
            }

            if (!empty(trim($this->filter_keyword))) {
                $queryNota->where(function($q) {
                    $q->where('kode_nota', 'LIKE', '%' . $this->filter_keyword . '%')
                      ->orWhereHas('pelanggan', function($q2) {
                          $q2->where('nama', 'LIKE', '%' . $this->filter_keyword . '%');
                      })
                      ->orWhereHas('marketing', function($q3) {
                          $q3->where('nama', 'LIKE', '%' . $this->filter_keyword . '%');
                      });
                });
            }
            $daftar_nota = $queryNota->orderBy('tanggal_transaksi', 'desc')->limit(50)->get();
        }

        $hasil_pencarian_produk = collect();
        if ($this->showReturModal && !empty(trim($this->search_produk_pengganti))) {
            $queryProduk = Produk::where('status_aktif', true);
            $terms = explode(' ', trim(strtolower($this->search_produk_pengganti)));
            
            foreach ($terms as $term) {
                $queryProduk->where('index_pencarian', 'LIKE', '%' . $term . '%');
            }
            $hasil_pencarian_produk = $queryProduk->limit(10)->get();
        }

        return view('livewire.transaksi.retur-penjualan', [
            'daftar_nota' => $daftar_nota,
            'hasil_pencarian_produk' => $hasil_pencarian_produk
        ]);
    }
}