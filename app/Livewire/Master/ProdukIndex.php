<?php

namespace App\Livewire\Master;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Kategori;

class ProdukIndex extends Component
{
    public $keyword = '';
    public $form_open = false;
    public $edit_id = null;

    // Data Master
    public $daftarKategori = [];
    public $atributDinamis = []; // Menampung atribut apa saja yang wajib diisi berdasarkan kategori

    // Field Form
    public $id_kategori = '';
    public $kode_barang = '';
    public $nama_produk = '';
    public $satuan = 'pcs';
    public $harga_jual_satuan = 0;
    public $lacak_stok = true;
    
    // Field JSON
    public $metadata_input = []; // Format: ['Merk' => 'CN', 'Ring' => '3']

    public function mount()
    {
        $this->daftarKategori = Kategori::orderBy('nama_kategori')->get();
    }

    // MAGIC HOOK LIVEWIRE: Otomatis terpanggil saat dropdown Kategori berubah
    public function updatedIdKategori($value)
    {
        $this->metadata_input = []; // Reset inputan JSON
        $this->atributDinamis = [];

        if ($value) {
            $kategori = Kategori::with('atribut')->find($value);
            if ($kategori) {
                $this->atributDinamis = $kategori->atribut;
                // Inisialisasi key array untuk wire:model
                foreach ($this->atributDinamis as $attr) {
                    $this->metadata_input[$attr->nama_atribut] = '';
                }
            }
        }
    }

    public function simpan()
    {
        // 1. Validasi Dasar
        $this->validate([
            'id_kategori' => 'required',
            'kode_barang' => 'required|unique:produk,kode_barang,' . $this->edit_id . ',id_produk',
            'nama_produk' => 'required|string|max:255',
            'satuan' => 'required|string',
            'harga_jual_satuan' => 'required|numeric|min:0',
            'lacak_stok' => 'boolean',
        ]);

        // 2. Validasi Atribut Dinamis (Pastikan semua spesifikasi terpilih)
        foreach ($this->atributDinamis as $attr) {
            if (empty($this->metadata_input[$attr->nama_atribut])) {
                $this->addError('metadata_input.' . $attr->nama_atribut, "Atribut {$attr->nama_atribut} wajib dipilih!");
                return;
            }
        }

        // 3. Gabungkan teks untuk mesin pencari POS (Fulltext Search Index)
        $metaString = !empty($this->metadata_input) ? implode(' ', array_values($this->metadata_input)) : '';
        $indexPencarian = strtolower($this->kode_barang . ' ' . $this->nama_produk . ' ' . $metaString);

        if ($this->edit_id) {
            // EDIT PRODUK (Stok tidak ikut diupdate karena dikunci sesuai PRD)
            Produk::find($this->edit_id)->update([
                'id_kategori' => $this->id_kategori,
                'kode_barang' => $this->kode_barang,
                'nama_produk' => $this->nama_produk,
                'satuan' => $this->satuan,
                'harga_jual_satuan' => $this->harga_jual_satuan,
                'lacak_stok' => $this->lacak_stok,
                'metadata' => empty($this->metadata_input) ? null : $this->metadata_input,
                'index_pencarian' => $indexPencarian,
            ]);
            session()->flash('sukses', 'Data produk berhasil diubah.');
        } else {
            // TAMBAH PRODUK BARU
            Produk::create([
                'id_kategori' => $this->id_kategori,
                'kode_barang' => $this->kode_barang,
                'nama_produk' => $this->nama_produk,
                'satuan' => $this->satuan,
                'harga_jual_satuan' => $this->harga_jual_satuan,
                'lacak_stok' => $this->lacak_stok,
                'stok_saat_ini' => 0, // Stok awal wajib 0, diisi lewat menu Adjust Stok!
                'metadata' => empty($this->metadata_input) ? null : $this->metadata_input,
                'index_pencarian' => $indexPencarian,
            ]);
            session()->flash('sukses', 'Produk baru berhasil ditambahkan! Silakan lakukan penyesuaian stok awal di menu Adjust Stok.');
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
        $this->metadata_input = $produk->metadata ?? [];
        
        // Pancing hook kategori untuk meload UI Atribut
        $this->updatedIdKategori($this->id_kategori);
        
        // Tumpuk ulang nilai metadata setelah hook berjalan
        $this->metadata_input = $produk->metadata ?? [];

        $this->form_open = true;
    }

    public function toggleAktif($id)
    {
        $produk = Produk::find($id);
        $produk->update(['status_aktif' => !$produk->status_aktif]);
    }

    public function resetForm()
    {
        $this->reset(['edit_id', 'id_kategori', 'kode_barang', 'nama_produk', 'satuan', 'harga_jual_satuan', 'metadata_input', 'atributDinamis']);
        $this->lacak_stok = true;
        $this->form_open = false;
    }

    public function render()
    {
        // Fitur Pencarian Cepat
        $query = Produk::with('kategori')->orderBy('status_aktif', 'desc')->latest();
        
        if (strlen($this->keyword) >= 2) {
            $query->whereRaw("MATCH(index_pencarian) AGAINST(? IN BOOLEAN MODE)", [$this->keyword . '*']);
        }

        $daftarProduk = $query->paginate(20);

        return view('livewire.master.produk-index', [
            'daftarProduk' => $daftarProduk
        ]);
    }
}