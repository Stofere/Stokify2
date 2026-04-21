<?php

namespace App\Livewire\Transaksi;

use Livewire\Component;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Marketing;
use App\Services\TransactionService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class KasirPos extends Component
{
    public $keyword = '';
    
    // --- STATE PELANGGAN (WAJIB) ---
    public $id_pelanggan = null;
    public $pelangganTerpilihNama = '';
    public $searchPelanggan = '';
    
    // State Pelanggan Baru (Disimpan di Frontend dulu)
    public $is_pelanggan_baru = false;
    public $pelanggan_baru_nama = '';
    public $pelanggan_baru_telepon = '';
    public $pelanggan_baru_alamat = '';

    // --- STATE MARKETING (WAJIB) ---
    public $id_marketing = null;
    public $marketingTerpilihNama = '';
    public $searchMarketing = '';

    public $catatan = '';
    public $keranjang = []; 
    public $total_belanja = 0;
    
    public $showConfirmModal = false;

    // --- FUNGSI PELANGGAN ---
    public function pilihPelanggan($id, $nama)
    {
        $this->is_pelanggan_baru = false;
        $this->id_pelanggan = $id;
        $this->pelangganTerpilihNama = $nama;
        $this->searchPelanggan = '';
        $this->resetErrorBag('pelanggan_wajib');
    }

    public function hapusPelanggan()
    {
        $this->id_pelanggan = null;
        $this->pelangganTerpilihNama = '';
        $this->is_pelanggan_baru = false;
        $this->pelanggan_baru_nama = '';
        $this->pelanggan_baru_telepon = '';
        $this->pelanggan_baru_alamat = '';
    }

    public function setPelangganBaru()
    {
        $this->validate(['searchPelanggan' => 'required|min:2|max:255']);
        
        $this->is_pelanggan_baru = true;
        $this->pelanggan_baru_nama = $this->searchPelanggan;
        $this->pelangganTerpilihNama = $this->searchPelanggan . ' (Pelanggan Baru)';
        $this->id_pelanggan = null;
        $this->searchPelanggan = '';
        $this->resetErrorBag('pelanggan_wajib');
    }

    // --- FUNGSI MARKETING ---
    public function pilihMarketing($id, $nama)
    {
        $this->id_marketing = $id;
        $this->marketingTerpilihNama = $nama;
        $this->searchMarketing = '';
        $this->resetErrorBag('marketing_wajib');
    }

    public function hapusMarketing()
    {
        $this->id_marketing = null;
        $this->marketingTerpilihNama = '';
    }

    // --- FUNGSI KERANJANG ---
    public function tambahKeKeranjang(int $idProduk)
    {
        $produk = Produk::find($idProduk);
        if (!$produk || !$produk->status_aktif) return;

        $index = collect($this->keranjang)->search(fn($item) => $item['id_produk'] === $idProduk);

        if ($index !== false) {
            $qtyBaru = $this->keranjang[$index]['jumlah'] + 1;
            if ($produk->lacak_stok && $qtyBaru > $produk->stok_saat_ini) {
                session()->flash('error', "Stok {$produk->nama_produk} tidak mencukupi!");
                return;
            }
            $this->keranjang[$index]['jumlah'] = $qtyBaru;
            $this->keranjang[$index]['subtotal'] = $qtyBaru * $this->keranjang[$index]['harga_satuan'];
        } else {
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

    // FIX: Hook ini otomatis jalan saat user mengetik angka di kolom input keranjang (Reaktivitas Harga)
    public function updatedKeranjang($value, $key)
    {
        $parts = explode('.', $key); // format: $key = "0.jumlah"
        if (count($parts) == 2 && $parts[1] == 'jumlah') {
            $index = $parts[0];
            $qty = (float) $value;
            $satuan = $this->keranjang[$index]['satuan'];

            // BACKEND VALIDASI: Anti Desimal (Cegah 0.1 di Pcs)
            if (in_array($satuan, ['pcs', 'biji', 'unit', 'buah'])) {
                $qty = floor($qty); // Paksa buang desimalnya
                $this->keranjang[$index]['jumlah'] = $qty; // Update kembali ke UI
            }

            // Validasi Maksimal Stok di UI
            if ($this->keranjang[$index]['lacak_stok'] && $qty > $this->keranjang[$index]['max_stok']) {
                $qty = $this->keranjang[$index]['max_stok'];
                $this->keranjang[$index]['jumlah'] = $qty;
                session()->flash('error', "Stok tidak mencukupi! Maksimal: " . $qty);
            }

            // Update subtotal dan total uang
            $this->keranjang[$index]['subtotal'] = $qty * $this->keranjang[$index]['harga_satuan'];
            $this->hitungTotal();
        }
    }

    public function hapusItem(int $index)
    {
        unset($this->keranjang[$index]);
        $this->keranjang = array_values($this->keranjang);
        $this->hitungTotal();
    }

    public function hitungTotal()
    {
        $this->total_belanja = collect($this->keranjang)->sum('subtotal');
    }

    // --- FUNGSI PROSES CHECKOUT ---
    public function konfirmasiPembayaran()
    {
        if (empty($this->keranjang)) {
            $this->addError('keranjang', 'Keranjang belanja kosong.');
            return;
        }

        // VALIDASI WAJIB: Pelanggan & Marketing Harus Ada
        if (!$this->id_pelanggan && !$this->is_pelanggan_baru) {
            $this->addError('pelanggan_wajib', 'Pilih atau tambahkan pelanggan terlebih dahulu!');
            return;
        }

        if (!$this->id_marketing) {
            $this->addError('marketing_wajib', 'Sales / Marketing wajib dipilih!');
            return;
        }

        // Pastikan nama termuat untuk tampilan di Modal
        if ($this->id_pelanggan && empty($this->pelangganTerpilihNama)) {
            $this->pelangganTerpilihNama = Pelanggan::find($this->id_pelanggan)->nama;
        }
        if ($this->id_marketing && empty($this->marketingTerpilihNama)) {
            $this->marketingTerpilihNama = Marketing::find($this->id_marketing)->nama;
        }

        // DOUBLE CHECK BACKEND: mencegah if website nya ngebug masih bisa masukin desimal ke satuan selain meter,kg
        foreach ($this->keranjang as $item) {
            if (in_array($item['satuan'], ['pcs', 'biji', 'unit', 'buah']) && fmod($item['jumlah'], 1) !== 0.0) {
                $this->addError('checkout', "Barang {$item['nama_produk']} satuannya Pcs, tidak boleh ada koma/desimal!");
                return;
            }
        }

        $this->showConfirmModal = true;
    }

    public function prosesPembayaranFinal(TransactionService $transactionService)
    {
        $lock = Cache::lock('proses-bayar:' . Auth::id(), 10);

        if ($lock->get()) {
            try {
                // PENCEGAHAN SPAM: Menggunakan DB Transaction agar pembuatan Pelanggan & Nota berjalan dalam 1 antrean aman
                DB::beginTransaction();
                try {
                    $id_pelanggan_final = $this->id_pelanggan;

                    // Jika pelanggan baru, buat datanya di database SEKARANG
                    if ($this->is_pelanggan_baru) {
                        $newPlg = Pelanggan::create([
                            'nama' => $this->pelanggan_baru_nama,
                            'telepon' => $this->pelanggan_baru_telepon,
                            'alamat' => $this->pelanggan_baru_alamat,
                            'aktif' => true
                        ]);
                        $id_pelanggan_final = $newPlg->id_pelanggan;
                    }

                    $dataNota = [
                        'id_pelanggan' => $id_pelanggan_final,
                        'id_marketing' => $this->id_marketing,
                        'catatan' => $this->catatan,
                    ];

                    // Lempar ke otak bisnis untuk diproses
                    $nota = $transactionService->createPenjualan(Auth::id(), $dataNota, $this->keranjang);
                    
                    DB::commit(); // Konfirmasi semua perubahan database (Pelanggan + Transaksi)

                    session()->flash('sukses', "Transaksi berhasil! Nomor Nota: " . $nota->kode_nota);
                    
                    // Reset seluruh State
                    $this->reset([
                        'keranjang', 'total_belanja', 'catatan', 'keyword', 'showConfirmModal',
                        'id_pelanggan', 'pelangganTerpilihNama', 'searchPelanggan', 'is_pelanggan_baru', 'pelanggan_baru_nama', 'pelanggan_baru_telepon', 'pelanggan_baru_alamat',
                        'id_marketing', 'marketingTerpilihNama', 'searchMarketing'
                    ]);

                } catch (Exception $e) {
                    DB::rollBack(); // Batalkan pembuatan pelanggan & nota jika ada yang error (misal stok tiba-tiba habis)
                    $this->showConfirmModal = false;
                    $this->addError('checkout', $e->getMessage());
                }
            } finally {
                $lock->release();
            }
        } else {
            session()->flash('error', 'Transaksi sebelumnya masih diproses. Harap tunggu sebentar.');
        }
    }

    public function render()
    {
        // 1. Pencarian Produk
        $query = Produk::where('status_aktif', true);
        if (!empty(trim($this->keyword))) {
            $terms = explode(' ', trim(strtolower($this->keyword)));
            foreach ($terms as $term) {
                $query->where('index_pencarian', 'LIKE', '%' . $term . '%');
            }
            $hasilPencarian = $query->limit(20)->get();
        } else {
            $hasilPencarian = $query->latest()->limit(12)->get();
        }

        // 2. Pencarian Cepat Pelanggan
        // FIX: Pencarian Cepat (Dibuat lebih ringan & tanpa debounce di blade nanti)
        $hasilPelanggan = collect();
        if (strlen($this->searchPelanggan) > 0) {
            // Pencarian di memory database yang ringan
            $hasilPelanggan = Pelanggan::where('aktif', true)->where('nama', 'like', $this->searchPelanggan . '%')->limit(5)->get();
            if($hasilPelanggan->count() < 5) {
                $hasilPelanggan = $hasilPelanggan->merge(Pelanggan::where('aktif', true)->where('nama', 'like', '%' . $this->searchPelanggan . '%')->limit(5 - $hasilPelanggan->count())->get())->unique('id_pelanggan');
            }
        }

        // 3. Pencarian Cepat Marketing (Hanya mencari yang sudah ada)
        $hasilMarketing = collect();
        if (strlen($this->searchMarketing) > 0) {
            $hasilMarketing = Marketing::where('aktif', true)->where('nama', 'like', $this->searchMarketing . '%')->limit(5)->get();
            if($hasilMarketing->count() < 5) {
                $hasilMarketing = $hasilMarketing->merge(Marketing::where('aktif', true)->where('nama', 'like', '%' . $this->searchMarketing . '%')->limit(5 - $hasilMarketing->count())->get())->unique('id_marketing');
            }
        }

        return view('livewire.transaksi.kasir-pos', [
            'daftarProduk' => $hasilPencarian,
            'hasilPelanggan' => $hasilPelanggan,
            'hasilMarketing' => $hasilMarketing,
        ]);
    }
}