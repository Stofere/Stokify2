<div class="p-6 max-w-7xl mx-auto">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">Katalog Barang & Stok</h2>
            <p class="text-gray-500 mt-1">Kelola spesifikasi barang dan pantau riwayat mutasi gudang secara langsung.</p>
        </div>
        @if(!$form_open && !$stok_modal_open && !$modal_detail_nota_open)
            <button wire:click="$set('form_open', true)" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl shadow-lg font-bold flex items-center gap-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Barang Baru
            </button>
        @endif
    </div>

    <!-- Alert Notifikasi -->
    @if(session()->has('sukses'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 mb-6 rounded-xl shadow-sm flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <span class="block text-sm">{{ session('sukses') }}</span>
            </div>
        </div>
    @endif

    <!-- FORMULIR TAMBAH/EDIT BARANG -->
    @if($form_open)
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 border border-gray-100">
            <!-- (Kode Form Sama Persis dengan Sebelumnya) -->
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-xl text-blue-800">{{ $edit_id ? 'Perbarui Data Barang' : 'Form Barang Baru' }}</h3>
                <button wire:click="resetForm" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            
            <div class="p-6">
                <!-- LANGKAH 1 -->
                <div class="mb-8 p-5 bg-gray-50 border rounded-xl relative">
                    <span class="absolute -top-3 -left-3 bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-bold shadow">1</span>
                    <label class="block text-gray-800 font-bold mb-2">Barang ini masuk kelompok kategori apa?</label>
                    <select wire:model.live="id_kategori" class="w-full md:w-1/2 border-gray-300 rounded-lg p-3 border focus:ring-blue-500 bg-white">
                        <option value="">-- Silakan Pilih Kategori --</option>
                        @foreach($daftarKategori as $kat)
                            <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- LANGKAH 2 & 3 -->
                @if($id_kategori)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="space-y-5 relative">
                            <span class="absolute -top-3 -left-5 bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-bold shadow hidden md:flex">2</span>
                            <h4 class="font-bold text-gray-700 border-b pb-2">Informasi Dasar Barang</h4>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">Kode Barang (Barcode / SKU)</label>
                                <input type="text" wire:model="kode_barang" placeholder="Contoh: FR-12-CN-GRS" class="w-full border-gray-300 rounded-lg p-3 border focus:ring-blue-500 uppercase bg-gray-50">
                                @error('kode_barang') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-1">Nama Barang (Sesuai Nota Kasir)</label>
                                <input type="text" wire:model="nama_produk" placeholder="Contoh: Fr 12 in CN Grs (3)" class="w-full border-gray-300 rounded-lg p-3 border focus:ring-blue-500">
                                @error('nama_produk') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-600 mb-1">Satuan Jual</label>
                                    <select wire:model="satuan" class="w-full border-gray-300 rounded-lg p-3 border">
                                        <option value="pcs">Pcs / Biji</option>
                                        <option value="meter">Meter</option>
                                        <option value="kg">Kilogram (Kg)</option>
                                        <option value="rol">Rol</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600 mb-1">Harga Jual (Rp)</label>
                                    <input type="number" wire:model="harga_jual_satuan" placeholder="Contoh: 150000" class="w-full border-gray-300 rounded-lg p-3 border">
                                    @error('harga_jual_satuan') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Spesifikasi Dinamis -->
                        <div class="space-y-5 relative bg-blue-50 p-5 rounded-xl border border-blue-100">
                            <span class="absolute -top-3 -left-3 bg-blue-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-bold shadow md:hidden">3</span>
                            <h4 class="font-bold text-blue-800 border-b border-blue-200 pb-2">Pilih Spesifikasi Khusus</h4>
                            
                            @if(count($atributDinamis) == 0)
                                <div class="text-gray-500 text-sm py-4 text-center bg-white rounded-lg border border-dashed">
                                    Kategori ini tidak punya spesifikasi khusus.
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($atributDinamis as $attr)
                                        <div>
                                            <label class="block text-sm font-bold text-blue-900 mb-1">{{ $attr->nama_atribut }}</label>
                                            <select wire:model="metadata_input.{{ $attr->nama_atribut }}" class="w-full border-blue-200 rounded-lg p-2 border bg-white focus:ring-blue-500">
                                                <option value="">-- Bebas / Tanpa {{ $attr->nama_atribut }} --</option>
                                                @foreach($attr->pilihan_opsi as $opsi)
                                                    <option value="{{ $opsi }}">{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t pt-6">
                        <label class="flex items-center gap-3 cursor-pointer bg-gray-50 p-3 rounded-lg border">
                            <input type="checkbox" wire:model="lacak_stok" class="w-6 h-6 text-blue-600 rounded">
                            <div>
                                <span class="font-bold text-gray-800 block">Lacak Stok Barang Ini</span>
                            </div>
                        </label>
                        
                        <div class="flex gap-3">
                            <button wire:click="resetForm" class="bg-white hover:bg-gray-100 border text-gray-700 px-6 py-3 rounded-xl font-bold">BATAL</button>
                            <button wire:click="simpan" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-black shadow-lg">SIMPAN BARANG</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- TABEL DATA BARANG UTAMA -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden {{ ($stok_modal_open || $modal_detail_nota_open) ? 'hidden' : '' }}">
        <div class="bg-gray-50 p-4 border-b border-gray-200">
            <input type="text" wire:model.live.debounce.500ms="keyword" placeholder="🔍 Cari Kode, Nama, Merk..." class="block w-full md:w-1/2 p-3 border border-gray-300 rounded-xl bg-white focus:ring-blue-500 shadow-inner">
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b-2 border-gray-100 text-gray-500 text-sm tracking-wider">
                        <th class="p-4 font-bold">Detail Barang</th>
                        <th class="p-4 font-bold text-right">Harga Jual</th>
                        <th class="p-4 font-bold text-center">Stok Gudang</th>
                        <th class="p-4 font-bold text-center">Status</th>
                        <th class="p-4 font-bold w-48 text-center">Aksi / Menu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($daftarProduk as $prod)
                    <tr class="hover:bg-blue-50 {{ !$prod->status_aktif ? 'bg-red-50/50' : '' }}">
                        <td class="p-4">
                            <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-1 rounded border">{{ $prod->kode_barang }}</span>
                            <span class="text-xs text-gray-500 ml-2 font-bold">{{ $prod->kategori->nama_kategori }}</span>
                            <p class="font-bold text-gray-800 text-base mt-2">{{ $prod->nama_produk }}</p>
                            @if($prod->metadata)
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach($prod->metadata as $key => $val)
                                        <span class="bg-blue-100 text-blue-700 text-[11px] px-2 py-0.5 rounded-full font-semibold">{{ $val }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="p-4 text-right font-black text-green-700 text-lg">Rp {{ number_format($prod->harga_jual_satuan, 0, ',', '.') }}</td>
                        <td class="p-4 text-center">
                            @if($prod->lacak_stok)
                                <div class="bg-gray-100 inline-block px-3 py-1.5 rounded-lg border {{ $prod->stok_saat_ini <= 0 ? 'border-red-300 bg-red-50 text-red-600' : 'border-gray-200 text-gray-800' }}">
                                    <span class="font-black text-lg">{{ $prod->stok_display }}</span>
                                    <span class="text-xs uppercase ml-1">{{ $prod->satuan }}</span>
                                </div>
                            @else
                                <span class="bg-gray-200 text-gray-500 text-xs px-2 py-1 rounded font-bold uppercase">Tanpa Stok</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($prod->status_aktif)
                                <button wire:click="toggleAktif({{ $prod->id_produk }})" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-red-100 transition">Tampil di Kasir</button>
                            @else
                                <button wire:click="toggleAktif({{ $prod->id_produk }})" class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-green-100 transition">Disembunyikan</button>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex flex-col gap-2">
                                @if($prod->lacak_stok)
                                    <button wire:click="bukaModalStok({{ $prod->id_produk }})" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-lg font-bold text-xs border border-indigo-200 transition-colors flex justify-center items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Buku Stok
                                    </button>
                                @endif
                                <button wire:click="edit({{ $prod->id_produk }})" class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-3 py-2 rounded-lg border border-blue-200 transition text-xs">Edit Profil</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-12 text-center text-gray-500 font-bold">Tidak ada barang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50">{{ $daftarProduk->links() }}</div>
    </div>


    <!-- ==================================================================== -->
    <!-- MODAL / HALAMAN RIWAYAT & ADJUST STOK -->
    <!-- ==================================================================== -->
    @if($stok_modal_open && $produk_stok_aktif)
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border-2 border-indigo-500 mb-8 {{ $modal_detail_nota_open ? 'hidden' : '' }}">
            <div class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="font-black text-2xl flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Buku Mutasi & Koreksi Stok Gudang
                    </h3>
                    <p class="text-indigo-200 text-sm mt-1">Barang: <span class="font-bold text-white">{{ $produk_stok_aktif->nama_produk }}</span> ({{ $produk_stok_aktif->kode_barang }})</p>
                </div>
                <button wire:click="tutupModalStok" class="bg-indigo-800 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-bold transition">TUTUP BUKU STOK</button>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-0">
                
                <!-- KOLOM KIRI: Form Adjust Stok / Opname -->
                <div class="p-6 bg-gray-50 border-r border-gray-200">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 text-center">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Sisa Fisik Sistem</p>
                        <p class="text-5xl font-black text-indigo-700 my-2">{{ $produk_stok_aktif->stok_display }} <span class="text-lg text-gray-500">{{ $produk_stok_aktif->satuan }}</span></p>
                    </div>

                    <h4 class="font-bold text-gray-800 mb-4 border-b pb-2">Form Koreksi Stok (Opname)</h4>
                    
                    @if(session()->has('sukses_stok'))
                        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-lg text-sm font-bold border border-green-200">{{ session('sukses_stok') }}</div>
                    @endif
                    @error('sistem_stok')
                        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-lg text-sm font-bold border border-red-200">{{ $message }}</div>
                    @enderror

                    <form wire:submit="prosesAdjustStok" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tipe Mutasi</label>
                            <select wire:model="tipe_penyesuaian" class="w-full border-gray-300 rounded-lg p-2.5 border bg-white focus:ring-indigo-500">
                                <option value="KOREKSI_MINUS">Barang KELUAR (Rusak/Hilang/Terjual Offline)</option>
                                <option value="KOREKSI_PLUS">Barang MASUK (Kirim Pabrik/Kelebihan Opname)</option>
                            </select>
                            @error('tipe_penyesuaian') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Fisik ({{ $produk_stok_aktif->satuan }})</label>
                            <input type="number" step="0.01" wire:model="jumlah_adjust" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 text-lg font-bold text-center">
                            @error('jumlah_adjust') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Keterangan / Alasan Wajib</label>
                            <textarea wire:model="keterangan_adjust" rows="2" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 text-sm" placeholder="Contoh: Barang datang dari supplier, Nota No..."></textarea>
                            @error('keterangan_adjust') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg border border-red-200 mt-2">
                            <label class="block text-xs font-black text-red-700 mb-2 uppercase tracking-wide">Otorisasi Keamanan</label>
                            <input type="password" wire:model="password_admin" placeholder="Masukkan Password Akun Anda..." class="w-full border-red-300 rounded-lg p-2.5 border focus:ring-red-500 text-sm">
                            @error('password_admin') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 rounded-lg shadow-lg mt-4 transition-transform active:scale-95">
                            EKSEKUSI MUTASI STOK
                        </button>
                    </form>
                </div>

                <!-- KOLOM KANAN: Tabel Riwayat / CCTV Gudang -->
                <div class="xl:col-span-2 p-0 bg-white flex flex-col h-full">
                    
                    <!-- AREA FILTER TANGGAL -->
                    <div class="p-4 border-b bg-gray-50 flex flex-wrap gap-4 items-center justify-between">
                        <h4 class="font-bold text-gray-800">CCTV Rekam Jejak Stok</h4>
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Dari Tanggal</label>
                                <input wire:model.live="riwayat_tgl_mulai" type="date" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Sampai Tanggal</label>
                                <input wire:model.live="riwayat_tgl_akhir" type="date" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse text-sm min-w-[600px]">
                            <thead class="bg-white shadow-sm border-b">
                                <tr class="text-gray-500 bg-gray-50 uppercase tracking-wider text-[11px]">
                                    <th class="p-3">Waktu & Pelaku</th>
                                    <th class="p-3">Tipe</th>
                                    <th class="p-3 text-right">Mutasi</th>
                                    <th class="p-3 text-center">Sisa</th>
                                    <th class="p-3">Keterangan Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @if($riwayatStok)
                                    @forelse($riwayatStok as $riwayat)
                                        <tr class="hover:bg-blue-50 transition-colors">
                                            <td class="p-3">
                                                <p class="font-bold text-gray-800">{{ $riwayat->created_at->format('d/m/Y H:i') }}</p>
                                                <p class="text-xs text-blue-600 font-semibold">{{ $riwayat->user->name }}</p>
                                            </td>
                                            <td class="p-3">
                                                @php
                                                    $warna = 'bg-gray-100 text-gray-600 border-gray-200';
                                                    if(in_array($riwayat->tipe, ['MASUK', 'KOREKSI_PLUS'])) $warna = 'bg-green-100 text-green-700 border-green-200';
                                                    if(in_array($riwayat->tipe, ['KELUAR', 'KOREKSI_MINUS'])) $warna = 'bg-red-100 text-red-700 border-red-200';
                                                @endphp
                                                <span class="{{ $warna }} px-2 py-1 rounded font-bold text-[10px] uppercase border">{{ str_replace('_', ' ', $riwayat->tipe) }}</span>
                                            </td>
                                            <td class="p-3 text-right font-black {{ in_array($riwayat->tipe, ['MASUK', 'KOREKSI_PLUS', 'AWAL']) ? 'text-green-600' : 'text-red-600' }}">
                                                {{ in_array($riwayat->tipe, ['MASUK', 'KOREKSI_PLUS', 'AWAL']) ? '+' : '-' }}{{ fmod($riwayat->jumlah, 1) == 0 ? (int)$riwayat->jumlah : $riwayat->jumlah }}
                                            </td>
                                            <td class="p-3 text-center font-bold text-gray-800 bg-gray-50">
                                                {{ fmod($riwayat->stok_sesudah, 1) == 0 ? (int)$riwayat->stok_sesudah : $riwayat->stok_sesudah }}
                                            </td>
                                            <td class="p-3 text-xs">
                                                <!-- TOMBOL BUKA DETAIL NOTA -->
                                                @if($riwayat->id_transaksi_penjualan)
                                                    <button wire:click="lihatDetailNota({{ $riwayat->id_transaksi_penjualan }}, 'POS')" class="w-full text-left bg-blue-50 hover:bg-blue-100 border border-blue-200 p-2 rounded block transition shadow-sm">
                                                        <span class="font-bold text-blue-800 block">🛒 POS: {{ $riwayat->transaksiPenjualan->kode_nota }}</span>
                                                        <span class="text-gray-600 block mt-0.5">👤 Pelanggan: {{ $riwayat->transaksiPenjualan->pelanggan->nama ?? 'Umum' }}</span>
                                                        @if($riwayat->transaksiPenjualan->marketing)
                                                            <span class="text-gray-500 block">👔 Marketing: {{ $riwayat->transaksiPenjualan->marketing->nama }}</span>
                                                        @endif
                                                        <span class="text-blue-500 text-[10px] mt-1 font-bold underline">Lihat Detail Nota &rarr;</span>
                                                    </button>
                                                @elseif($riwayat->id_retur)
                                                    <button wire:click="lihatDetailNota({{ $riwayat->id_retur }}, 'RETUR')" class="w-full text-left bg-purple-50 hover:bg-purple-100 border border-purple-200 p-2 rounded block transition shadow-sm">
                                                        <span class="font-bold text-purple-800 block">🔄 Retur: {{ $riwayat->transaksiRetur->kode_retur }}</span>
                                                        <span class="text-gray-600 block mt-0.5">Nota Asal: {{ $riwayat->transaksiRetur->transaksiPenjualan->kode_nota ?? '-' }}</span>
                                                        <span class="text-purple-500 text-[10px] mt-1 font-bold underline">Lihat Detail Retur &rarr;</span>
                                                    </button>
                                                @else
                                                    <div class="bg-gray-100 border border-gray-200 p-2 rounded text-gray-700">
                                                        <span class="font-bold text-gray-800 block mb-1">✍️ Penyesuaian Manual</span>
                                                        {{ $riwayat->keterangan }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="p-8 text-center text-gray-400 font-bold">Tidak ada riwayat mutasi di tanggal tersebut.</td></tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- PAGINATION RIWAYAT (Terpisah dari pagination produk) -->
                    <div class="p-3 border-t bg-white">
                        @if($riwayatStok)
                            {{ $riwayatStok->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================================================================== -->
    <!-- MODAL POP-UP: BACA DETAIL NOTA (Dari klik Riwayat Stok) -->
    <!-- ==================================================================== -->
    @if($modal_detail_nota_open && $detail_nota_aktif)
        <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- HEADER MODAL -->
                <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            @if($tipe_nota_aktif == 'POS') 🛒 Detail Nota Penjualan @else 🔄 Detail Nota Retur @endif
                        </h3>
                        <p class="text-gray-300 text-sm mt-1">Kode: <span class="font-bold text-white">{{ $detail_nota_aktif->kode_nota ?? $detail_nota_aktif->kode_retur }}</span></p>
                    </div>
                    <button wire:click="tutupDetailNota" class="bg-gray-700 hover:bg-red-600 text-white px-4 py-2 rounded font-bold transition">&times; TUTUP</button>
                </div>

                <!-- BODY MODAL (SCROLLABLE) -->
                <div class="p-6 overflow-y-auto bg-gray-50 flex-1">
                    
                    @if($tipe_nota_aktif == 'POS')
                        <!-- TAMPILAN JIKA ITU NOTA POS -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Waktu Transaksi</p>
                                <p class="font-bold text-gray-800">{{ $detail_nota_aktif->tanggal_transaksi->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Kasir / Admin</p>
                                <p class="font-bold text-gray-800">{{ $detail_nota_aktif->user->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Pelanggan</p>
                                <p class="font-bold text-blue-700">{{ $detail_nota_aktif->pelanggan->nama ?? 'Walk-in (Umum)' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Sales / Marketing</p>
                                <p class="font-bold text-gray-800">{{ $detail_nota_aktif->marketing->nama ?? '-' }}</p>
                            </div>
                        </div>

                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-2">Daftar Barang Terjual</h4>
                        <table class="w-full text-left text-sm bg-white border rounded shadow-sm">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="p-3">Nama Barang</th>
                                    <th class="p-3 text-center">Jumlah Beli</th>
                                    <th class="p-3 text-right">Harga Satuan</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($detail_nota_aktif->detailPenjualan as $det)
                                    <tr>
                                        <td class="p-3 font-bold text-gray-800">{{ $det->produk->nama_produk }}</td>
                                        <td class="p-3 text-center">
                                            {{ fmod($det->jumlah, 1) == 0 ? (int)$det->jumlah : $det->jumlah }} {{ $det->satuan_saat_jual }}
                                            @if($det->jumlah_diretur > 0)
                                                <span class="block text-[10px] text-red-500 font-bold mt-1">Diretur: {{ fmod($det->jumlah_diretur, 1) == 0 ? (int)$det->jumlah_diretur : $det->jumlah_diretur }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right text-gray-600">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-bold text-green-700">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="p-3 text-right font-bold uppercase text-gray-600">Total Harga:</td>
                                    <td class="p-3 text-right font-black text-xl text-green-700">Rp {{ number_format($detail_nota_aktif->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                    @elseif($tipe_nota_aktif == 'RETUR')
                        <!-- TAMPILAN JIKA ITU NOTA RETUR -->
                        <div class="grid grid-cols-2 gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Waktu Retur & Pelaku</p>
                                <p class="font-bold text-gray-800">{{ $detail_nota_aktif->tanggal_retur->format('d M Y, H:i') }} (Oleh: {{ $detail_nota_aktif->user->name ?? '-' }})</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Referensi Nota POS Asli</p>
                                <p class="font-bold text-blue-700 underline cursor-pointer" wire:click="lihatDetailNota({{ $detail_nota_aktif->transaksiPenjualan->id_transaksi_penjualan }}, 'POS')">
                                    {{ $detail_nota_aktif->transaksiPenjualan->kode_nota ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-2">Rincian Penukaran Barang</h4>
                        <div class="space-y-3">
                            @foreach($detail_nota_aktif->detailRetur as $detRetur)
                                <div class="bg-white border rounded p-4 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                                    <div class="flex-1 w-full bg-red-50 p-3 rounded border border-red-100 text-sm">
                                        <p class="text-[10px] font-bold text-red-500 uppercase mb-1">Kembali dari Pelanggan</p>
                                        <p class="font-bold text-red-800">{{ $detRetur->produkDikembalikan->nama_produk }}</p>
                                        <p class="text-xs mt-1">Qty: <span class="font-bold">{{ fmod($detRetur->jumlah, 1) == 0 ? (int)$detRetur->jumlah : $detRetur->jumlah }}</span> | Kondisi: <span class="font-bold">{{ $detRetur->kondisi_barang_dikembalikan }}</span></p>
                                    </div>
                                    <div class="text-gray-400 font-bold text-xl">&rarr;</div>
                                    <div class="flex-1 w-full bg-green-50 p-3 rounded border border-green-100 text-sm">
                                        <p class="text-[10px] font-bold text-green-600 uppercase mb-1">Pengganti dari Toko</p>
                                        <p class="font-bold text-green-800">{{ $detRetur->produkPengganti->nama_produk }}</p>
                                        <p class="text-xs mt-1">Qty: <span class="font-bold">{{ fmod($detRetur->jumlah, 1) == 0 ? (int)$detRetur->jumlah : $detRetur->jumlah }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 p-4 rounded-lg bg-white border shadow-sm">
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status Keuangan (Selisih Biaya Retur)</p>
                            @if($detail_nota_aktif->total_biaya_retur > 0)
                                <p class="text-xl font-black text-orange-700">Pelanggan Nambah: Rp {{ number_format(abs($detail_nota_aktif->total_biaya_retur), 0, ',', '.') }}</p>
                            @elseif($detail_nota_aktif->total_biaya_retur < 0)
                                <p class="text-xl font-black text-green-700">Toko Kembalikan Uang: Rp {{ number_format(abs($detail_nota_aktif->total_biaya_retur), 0, ',', '.') }}</p>
                            @else
                                <p class="text-xl font-black text-gray-700">Tukar Guling (Rp 0 / Pas)</p>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>