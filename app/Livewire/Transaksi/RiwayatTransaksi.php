<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransaksiPenjualan;
use App\Models\TransaksiRetur;

class RiwayatTransaksi extends Component
{
    use WithPagination;

    public $activeTab = 'POS'; // 'POS' atau 'RETUR'
    
    // Filter
    public $tgl_mulai;
    public $tgl_akhir;
    public $keyword = '';

    // Modal Detail
    public $modal_open = false;
    public $detail_nota = null;

    public function mount()
    {
        // Default filter: Hari ini
        $this->tgl_mulai = today()->format('Y-m-d');
        $this->tgl_akhir = today()->format('Y-m-d');
    }

    public function switchTab($tabName)
    {
        $this->activeTab = $tabName;
        $this->resetPage(); // Reset pagination saat pindah tab
    }

    public function updatingKeyword() { $this->resetPage(); }
    public function updatedTglMulai() { $this->resetPage(); }
    public function updatedTglAkhir() { $this->resetPage(); }

    public function lihatDetail($id, $tipe_paksa = null)
    {
        // Jika dari klik tombol "Lihat Nota Retur" di dalam Nota POS, tipe_paksa akan terisi 'RETUR'
        if ($tipe_paksa) {
            $this->activeTab = $tipe_paksa; // Paksa pindah tab state sementara di memori
        }

        if ($this->activeTab === 'POS') {
            // FIX: Tambahkan relasi transaksiRetur.detailRetur.produkPengganti untuk melacak jejak retur di dalam Nota POS
            $this->detail_nota = TransaksiPenjualan::with([
                'detailPenjualan.produk', 
                'user', 
                'pelanggan', 
                'marketing',
                'transaksiRetur.detailRetur.produkPengganti' // <--- RELASI BARU
            ])->find($id);
        } else {
            $this->detail_nota = TransaksiRetur::with([
                'detailRetur.produkDikembalikan', 
                'detailRetur.produkPengganti', 
                'user', 
                'transaksiPenjualan.pelanggan'
            ])->find($id);
        }
        
        $this->modal_open = true;
    }

    public function tutupModal()
    {
        $this->modal_open = false;
        $this->detail_nota = null;
    }

    public function render()
    {
        $queryPOS = TransaksiPenjualan::with(['user', 'pelanggan', 'marketing']);
        $queryRetur = TransaksiRetur::with(['user', 'transaksiPenjualan.pelanggan']);

        // Filter Tanggal
        if ($this->tgl_mulai && $this->tgl_akhir) {
            $queryPOS->whereBetween('tanggal_transaksi', [$this->tgl_mulai . ' 00:00:00', $this->tgl_akhir . ' 23:59:59']);
            $queryRetur->whereBetween('tanggal_retur', [$this->tgl_mulai . ' 00:00:00', $this->tgl_akhir . ' 23:59:59']);
        }

        // Filter Keyword
        if (!empty(trim($this->keyword))) {
            $kw = '%' . trim($this->keyword) . '%';
            
            $queryPOS->where(function($q) use ($kw) {
                $q->where('kode_nota', 'LIKE', $kw)
                  ->orWhereHas('pelanggan', fn($p) => $p->where('nama', 'LIKE', $kw))
                  ->orWhereHas('marketing', fn($m) => $m->where('nama', 'LIKE', $kw));
            });

            $queryRetur->where(function($q) use ($kw) {
                $q->where('kode_retur', 'LIKE', $kw)
                  ->orWhereHas('transaksiPenjualan.pelanggan', fn($p) => $p->where('nama', 'LIKE', $kw));
            });
        }

        return view('livewire.transaksi.riwayat-transaksi', [
            'daftarPos' => $this->activeTab === 'POS' ? $queryPOS->orderBy('id_transaksi_penjualan', 'desc')->paginate(10) : null,
            'daftarRetur' => $this->activeTab === 'RETUR' ? $queryRetur->orderBy('id_retur', 'desc')->paginate(10) : null,
        ]);
    }
}