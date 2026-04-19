<?php

namespace App\Livewire\Master;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\RiwayatStok;
use App\Models\TransaksiPenjualan;
use App\Models\TransaksiRetur;
use App\Services\StockService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProdukIndex extends Component
{
    use WithPagination;

    public $keyword = '';
    public $form_open = false;
    public $edit_id = null;

    // Data Master
    public $daftarKategori = [];
    public $atributDinamis = [];

    // Field Form Produk
    public $id_kategori = '';
    public $kode_barang = '';
    public $nama_produk = '';
    public $lokasi = '';
    public $satuan = 'pcs';
    public $harga_jual_satuan = 0;
    public $lacak_stok = true;
    public $metadata_input = [];

    // --- STATE UNTUK MODAL STOK (ADJUST & RIWAYAT) ---
    public $stok_modal_open = false;
    public $produk_stok_aktif = null;
    
    // Filter Riwayat Stok
    public $riwayat_tgl_mulai;
    public $riwayat_tgl_akhir;

    // Form Adjust Stok
    public $tipe_penyesuaian = 'KOREKSI_MINUS';
    public $jumlah_adjust = 0;
    public $keterangan_adjust = '';
    public $password_admin = '';

    // --- STATE UNTUK MODAL DETAIL NOTA (KLIK DARI RIWAYAT) ---
    public $modal_detail_nota_open = false;
    public $detail_nota_aktif = null;
    public $tipe_nota_aktif = ''; // 'POS' atau 'RETUR'

    public function mount()
    {
        $this->daftarKategori = Kategori::orderBy('nama_kategori')->get();
    }

    public function updatedIdKategori($value)
    {
        $this->metadata_input = [];
        $this->atributDinamis = [];

        if ($value) {
            $kategori = Kategori::with('atribut')->find($value);
            if ($kategori) {
                $this->atributDinamis = $kategori->atribut;
                foreach ($this->atributDinamis as $attr) {
                    // FIX BUG: Jika atribut adalah Tekstur, set default sebagai Array [] agar checkbox berfungsi
                    if ($attr->nama_atribut === 'Tekstur') {
                        $this->metadata_input[$attr->nama_atribut] = [];
                    } else {
                        $this->metadata_input[$attr->nama_atribut] = ''; 
                    }
                }
            }
        }
    }

    public function simpan()
    {
        $this->validate([
            'id_kategori' => 'required',
            'kode_barang' => 'required|unique:produk,kode_barang,' . $this->edit_id . ',id_produk',
            'nama_produk' => 'required|string|max:255',
            'satuan' => 'required|string',
            'harga_jual_satuan' => 'required|numeric|min:0',
            'lacak_stok' => 'boolean',
            'lokasi' => 'nullable|string|max:255', // (Jika Anda memakai fitur lokasi sebelumnya)
        ]);

        $metadataFinal = [];
        foreach ($this->atributDinamis as $attr) {
            $val = $this->metadata_input[$attr->nama_atribut] ?? null;
            if (!empty($val)) {
                // FIX: Jika inputnya berupa Array (seperti Checkbox Tekstur), gabungkan jadi string koma
                if(is_array($val)) {
                    $metadataFinal[$attr->nama_atribut] = implode(', ', $val);
                } else {
                    $metadataFinal[$attr->nama_atribut] = $val;
                }
            }
        }

        $metaString = !empty($metadataFinal) ? implode(' ', array_values($metadataFinal)) : '';
        $indexPencarian = strtolower($this->kode_barang . ' ' . $this->nama_produk . ' ' . $metaString);

        if ($this->edit_id) {
            Produk::find($this->edit_id)->update([
                'id_kategori' => $this->id_kategori,
                'kode_barang' => $this->kode_barang,
                'nama_produk' => $this->nama_produk,
                'satuan' => $this->satuan,
                'harga_jual_satuan' => $this->harga_jual_satuan,
                'lacak_stok' => $this->lacak_stok,
                'lokasi' => $this->lokasi ?? null,
                'metadata' => empty($metadataFinal) ? null : $metadataFinal,
                'index_pencarian' => $indexPencarian,
            ]);
            session()->flash('sukses', 'Data barang berhasil diperbarui.');
        } else {
            Produk::create([
                'id_kategori' => $this->id_kategori,
                'kode_barang' => $this->kode_barang,
                'nama_produk' => $this->nama_produk,
                'satuan' => $this->satuan,
                'harga_jual_satuan' => $this->harga_jual_satuan,
                'lacak_stok' => $this->lacak_stok,
                'stok_saat_ini' => 0,
                'lokasi' => $this->lokasi ?? null,
                'metadata' => empty($metadataFinal) ? null : $metadataFinal,
                'index_pencarian' => $indexPencarian,
            ]);
            session()->flash('sukses', 'Barang baru berhasil ditambahkan! Stok awal adalah 0.');
        }
        $this->resetForm();
    }

    public function edit($id)
    {
        $produk = Produk::find($id);
        $this->edit_id = $produk->id_produk;
        $this->id_kategori = $produk->id_kategori;
        $this->kode_barang = $produk->kode_barang;
        $this->nama_produk = $produk->nama_produk;
        $this->satuan = $produk->satuan;
        $this->harga_jual_satuan = $produk->harga_jual_satuan;
        $this->lacak_stok = $produk->lacak_stok;
        $this->lokasi = $produk->lokasi;
        
        $this->updatedIdKategori($this->id_kategori);
        $this->metadata_input = $produk->metadata ?? [];
        
        // FIX: Konversi kembali string berkomanya Tekstur menjadi Array agar Checkbox merespon saat Edit
        if(isset($this->metadata_input['Tekstur']) && is_string($this->metadata_input['Tekstur'])) {
            $this->metadata_input['Tekstur'] = array_map('trim', explode(',', $this->metadata_input['Tekstur']));
        }
        
        $this->form_open = true;
    }

    public function toggleAktif($id)
    {
        $produk = Produk::find($id);
        $produk->update(['status_aktif' => !$produk->status_aktif]);
    }

    public function resetForm()
    {
        $this->reset(['edit_id', 'id_kategori', 'kode_barang', 'nama_produk', 'lokasi', 'satuan', 'harga_jual_satuan', 'metadata_input', 'atributDinamis']);
        $this->lacak_stok = true;
        $this->form_open = false;
        $this->resetValidation();
    }

    public function updatingKeyword()
    {
        $this->resetPage(); // Reset pagination produk utama
    }

    // =========================================================================
    // FITUR MODAL STOK (ADJUST & RIWAYAT)
    // =========================================================================

    public function bukaModalStok($id_produk)
    {
        $this->produk_stok_aktif = Produk::find($id_produk);
        
        // Set default filter tanggal ke 30 hari terakhir
        $this->riwayat_tgl_mulai = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->riwayat_tgl_akhir = Carbon::now()->format('Y-m-d');
        
        $this->stok_modal_open = true;
        $this->resetFormAdjust();
        $this->resetPage('riwayatPage'); // Reset pagination khusus riwayat
    }

    public function tutupModalStok()
    {
        $this->stok_modal_open = false;
        $this->produk_stok_aktif = null;
        $this->resetFormAdjust();
    }

    // Reset pagination riwayat ketika tanggal diubah
    public function updatedRiwayatTglMulai() { $this->resetPage('riwayatPage'); }
    public function updatedRiwayatTglAkhir() { $this->resetPage('riwayatPage'); }

    private function resetFormAdjust()
    {
        $this->reset(['tipe_penyesuaian', 'jumlah_adjust', 'keterangan_adjust', 'password_admin']);
        $this->tipe_penyesuaian = 'KOREKSI_MINUS';
    }

    public function prosesAdjustStok(StockService $stockService)
    {
        $this->validate([
            'tipe_penyesuaian' => 'required|in:KOREKSI_PLUS,KOREKSI_MINUS',
            'jumlah_adjust' => 'required|numeric|min:0.01',
            'keterangan_adjust' => 'required|string|min:5',
            'password_admin' => 'required',
        ]);

        if (!Hash::check($this->password_admin, Auth::user()->password)) {
            $this->addError('password_admin', 'Password otorisasi salah!');
            return;
        }

        try {
            $stockService->adjustStokManual(
                $this->produk_stok_aktif->id_produk,
                Auth::id(),
                $this->tipe_penyesuaian,
                $this->jumlah_adjust,
                $this->keterangan_adjust
            );

            session()->flash('sukses_stok', "Stok fisik barang berhasil diperbarui.");
            $this->produk_stok_aktif->refresh();
            $this->resetFormAdjust();
            $this->resetPage('riwayatPage'); // Kembali ke halaman 1 riwayat setelah adjust

        } catch (Exception $e) {
            $this->addError('sistem_stok', $e->getMessage());
        }
    }

    // =========================================================================
    // FITUR LIHAT DETAIL NOTA DARI RIWAYAT
    // =========================================================================
    public function lihatDetailNota($id_transaksi, $tipe)
    {
        $this->tipe_nota_aktif = $tipe;
        
        if ($tipe === 'POS') {
            // FIX: Tambahkan relasi transaksiRetur
            $this->detail_nota_aktif = TransaksiPenjualan::with([
                'detailPenjualan.produk', 
                'user', 
                'pelanggan', 
                'marketing',
                'transaksiRetur.detailRetur.produkPengganti' // <--- RELASI BARU
            ])->find($id_transaksi);
        } elseif ($tipe === 'RETUR') {
            $this->detail_nota_aktif = TransaksiRetur::with([
                'detailRetur.produkDikembalikan', 
                'detailRetur.produkPengganti', 
                'user', 
                'transaksiPenjualan'
            ])->find($id_transaksi);
        }

        $this->modal_detail_nota_open = true;
    }

    public function tutupDetailNota()
    {
        $this->modal_detail_nota_open = false;
        $this->detail_nota_aktif = null;
    }

    // =========================================================================
    // RENDER METODE
    // =========================================================================
    public function render()
    {
        // 1. Query Produk Utama
        $query = Produk::with('kategori')->orderBy('status_aktif', 'desc')->latest();
        if (!empty(trim($this->keyword))) {
            $terms = explode(' ', trim(strtolower($this->keyword)));
            foreach ($terms as $term) {
                $query->where('index_pencarian', 'LIKE', '%' . $term . '%');
            }
        }

        // 2. Query Riwayat Stok (Hanya berjalan jika modal stok terbuka)
        $riwayat_stok_paginated = null;
        if ($this->stok_modal_open && $this->produk_stok_aktif) {
            $queryRiwayat = RiwayatStok::with(['user', 'transaksiPenjualan.pelanggan', 'transaksiPenjualan.marketing', 'transaksiRetur'])
                ->where('id_produk', $this->produk_stok_aktif->id_produk);

            if ($this->riwayat_tgl_mulai && $this->riwayat_tgl_akhir) {
                $queryRiwayat->whereBetween('created_at', [
                    $this->riwayat_tgl_mulai . ' 00:00:00',
                    $this->riwayat_tgl_akhir . ' 23:59:59'
                ]);
            }
            // Batasi 15 per halaman & beri nama page khusus (riwayatPage) agar tidak bentrok dengan pagination produk
            $riwayat_stok_paginated = $queryRiwayat->orderBy('id_riwayat', 'desc')->paginate(15, ['*'], 'riwayatPage');
        }

        return view('livewire.master.produk-index', [
            'daftarProduk' => $query->paginate(15),
            'riwayatStok' => $riwayat_stok_paginated
        ]);
    }
}