<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
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
        // Default filter ke 7 hari terakhir
        $this->filter_tanggal_mulai = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->filter_tanggal_akhir = Carbon::now()->format('Y-m-d');
    }

    // --- FUNGSI PILIH & KEMBALI NOTA ---
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

    // --- FUNGSI MODAL RETUR ---
    public function bukaModalRetur($id_detail)
    {
        $this->detailTerpilih = DetailPenjualan::with('produk')->find($id_detail);
        
        // Secara default, barang pengganti adalah barang itu sendiri (Jika pelanggan cuma mau tukar yang baru)
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
        $this->search_produk_pengganti = ''; // Tutup dropdown pencarian
    }

    // --- EKSEKUSI RETUR KE SERVICE ---
    public function prosesRetur(ReturnService $returnService)
    {
        $sisaMaksimal = $this->detailTerpilih->jumlah - $this->detailTerpilih->jumlah_diretur;

        // 1. Validasi Input
        $this->validate([
            'qty_retur' => "required|numeric|min:0.01|max:$sisaMaksimal",
            'kondisi_retur' => 'required|in:BAGUS,RUSAK',
            'catatan' => 'required|string|min:3',
            'password_admin' => 'required',
        ]);

        if (!$this->produk_pengganti) {
            $this->addError('search_produk_pengganti', 'Pilih barang pengganti terlebih dahulu!');
            return;
        }

        // 2. Keamanan Tingkat Tinggi (Otorisasi Password)
        if (!Hash::check($this->password_admin, Auth::user()->password)) {
            $this->addError('password_admin', 'Password otorisasi salah!');
            return;
        }

        // 3. Format Data untuk ReturnService (Sesuai dengan struktur Fase 5)
        $itemsRetur = [
            [
                'id_detail_penjualan' => $this->detailTerpilih->id_detail_penjualan,
                'id_produk_pengganti' => $this->produk_pengganti->id_produk,
                'jumlah' => $this->qty_retur,
                'kondisi_barang_dikembalikan' => $this->kondisi_retur,
            ]
        ];

        try {
            // Lempar ke Otak Bisnis (Service Layer)
            $returnService->prosesRetur(
                $this->notaTerpilih->id_transaksi_penjualan,
                Auth::id(),
                $itemsRetur,
                $this->catatan
            );

            session()->flash('sukses', 'Proses Retur Berhasil! Mutasi stok & uang telah disesuaikan.');
            
            // Refresh data nota agar tabel langsung berubah (Sisa barang berkurang)
            $this->notaTerpilih->refresh(); 
            $this->tutupModalRetur();

        } catch (Exception $e) {
            $this->addError('password_admin', $e->getMessage()); // Tampilkan error dari service
        }
    }

    public function render()
    {
        // 1. QUERY DAFTAR NOTA (Jika belum pilih nota)
        $daftar_nota = collect();
        if (!$this->notaTerpilih) {
            $queryNota = TransaksiPenjualan::with(['pelanggan'])
                ->whereBetween('tanggal_transaksi', [
                    $this->filter_tanggal_mulai . ' 00:00:00',
                    $this->filter_tanggal_akhir . ' 23:59:59'
                ]);

            if (!empty(trim($this->filter_keyword))) {
                $queryNota->where(function($q) {
                    $q->where('kode_nota', 'LIKE', '%' . $this->filter_keyword . '%')
                      ->orWhereHas('pelanggan', function($q2) {
                          $q2->where('nama', 'LIKE', '%' . $this->filter_keyword . '%');
                      });
                });
            }
            $daftar_nota = $queryNota->orderBy('tanggal_transaksi', 'desc')->limit(50)->get();
        }

        // 2. QUERY BARANG PENGGANTI (Pencarian Split-LIKE No Lag)
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