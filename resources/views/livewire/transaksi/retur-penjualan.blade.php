<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow mt-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Modul Tukar Barang (Retur)</h2>

    @if(session()->has('sukses')) 
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('sukses') }}</div> 
    @endif
    @error('sistem') 
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">{{ $message }}</div> 
    @enderror

    <!-- Langkah 1: Cari Nota -->
    <div class="flex gap-4 mb-6">
        <input type="text" wire:model="kode_nota" placeholder="Masukkan Kode Nota Jual... (Contoh: Nota_Jual_01-01-2024-001)" class="w-full border-gray-300 rounded p-2 border focus:ring-blue-500">
        <button wire:click="cariNota" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-bold">CARI NOTA</button>
    </div>
    @error('pencarian') <span class="text-red-500 text-sm -mt-4 block mb-4">{{ $message }}</span> @enderror

    <!-- Jika Nota Ditemukan -->
    @if($transaksi_aktif)
        <div class="bg-gray-50 border p-4 rounded mb-6">
            <h3 class="font-bold text-lg mb-2">Detail Nota: {{ $transaksi_aktif->kode_nota }}</h3>
            <p class="text-sm text-gray-600 mb-4">Tanggal: {{ $transaksi_aktif->tanggal_transaksi->format('d/m/Y H:i') }}</p>
            
            <!-- Form Input Retur per Baris -->
            <div class="bg-white p-4 border rounded shadow-sm">
                <h4 class="font-bold text-blue-800 mb-3">1. Pilih Barang yang Dikembalikan</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Barang dari Nota</label>
                        <select wire:model="pilih_detail_id" class="w-full border-gray-300 rounded p-2 border text-sm">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($transaksi_aktif->detailPenjualan as $det)
                                @php $sisa = $det->jumlah - $det->jumlah_diretur; @endphp
                                @if($sisa > 0)
                                    <option value="{{ $det->id_detail_penjualan }}">
                                        {{ $det->produk->nama_produk }} (Beli: {{ $det->jumlah }}, Sisa Bisa Diretur: {{ $sisa }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Qty Retur</label>
                        <input type="number" step="0.01" wire:model="qty_retur" class="w-full border-gray-300 rounded p-2 border text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Kondisi</label>
                        <select wire:model="kondisi" class="w-full border-gray-300 rounded p-2 border text-sm">
                            <option value="BAGUS">BAGUS (Masuk Gudang)</option>
                            <option value="RUSAK">RUSAK (Dibuang)</option>
                        </select>
                    </div>
                </div>

                <h4 class="font-bold text-green-700 mt-6 mb-3">2. Pilih Barang Pengganti</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2 relative">
                        @if(!$produk_pengganti)
                            <input type="text" wire:model.live.debounce.300ms="keyword_pengganti" placeholder="Cari barang baru pengganti..." class="w-full border-gray-300 rounded p-2 border text-sm">
                            @if(count($daftarPencarianPengganti) > 0)
                                <ul class="absolute z-10 w-full bg-white border mt-1 rounded shadow-lg max-h-40 overflow-y-auto text-sm">
                                    @foreach($daftarPencarianPengganti as $p)
                                        <li class="p-2 hover:bg-gray-100 cursor-pointer border-b" wire:click="cariProdukPengganti({{ $p->id_produk }})">
                                            {{ $p->nama_produk }} (Rp {{ number_format($p->harga_jual_satuan, 0, ',', '.') }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @else
                            <div class="flex justify-between items-center bg-green-50 border border-green-200 p-2 rounded text-sm">
                                <span class="font-bold text-green-800">{{ $produk_pengganti->nama_produk }}</span>
                                <button wire:click="$set('produk_pengganti', null)" class="text-xs bg-white px-2 py-1 rounded border">Ganti</button>
                            </div>
                        @endif
                    </div>
                    
                    <button wire:click="tambahItemRetur" class="bg-gray-800 text-white font-bold py-2 rounded shadow hover:bg-black text-sm">
                        + TAMBAHKAN KE LIST RETUR
                    </button>
                </div>
                @error('form_retur') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Keranjang Retur & Kalkulasi Biaya -->
        @if(count($items_retur) > 0)
            <div class="border rounded-lg p-4 mb-6">
                <h3 class="font-bold text-lg mb-4">Daftar Barang yang Ditukar</h3>
                <ul class="space-y-3 mb-4">
                    @foreach($items_retur as $index => $item)
                        <li class="flex justify-between items-center bg-gray-50 p-3 rounded border">
                            <div>
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded font-bold">KEMBALI: {{ $item['jumlah'] }}x {{ $item['nama_produk_lama'] }} ({{ $item['kondisi_barang_dikembalikan'] }})</span>
                                <span class="mx-2 text-gray-400">➡️</span>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">GANTI: {{ $item['jumlah'] }}x {{ $item['nama_produk_pengganti'] }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold {{ $item['selisih_biaya'] > 0 ? 'text-blue-600' : ($item['selisih_biaya'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                    @if($item['selisih_biaya'] > 0) Pelanggan Nambah: @elseif($item['selisih_biaya'] < 0) Toko Mengembalikan: @else Pas / Tukar Guling @endif
                                    Rp {{ number_format(abs($item['selisih_biaya']), 0, ',', '.') }}
                                </span>
                                <button wire:click="hapusItemRetur({{ $index }})" class="text-red-500 font-bold hover:underline">Hapus</button>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @php $totalSelisih = collect($items_retur)->sum('selisih_biaya'); @endphp
                <div class="flex justify-between items-center border-t pt-4">
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Catatan Retur (Opsional)</label>
                        <input type="text" wire:model="catatan" class="w-full border-gray-300 rounded p-2 border text-sm" placeholder="Alasan retur...">
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Selisih Biaya Retur:</p>
                        <p class="text-2xl font-bold {{ $totalSelisih > 0 ? 'text-blue-600' : ($totalSelisih < 0 ? 'text-red-600' : 'text-gray-800') }}">
                            @if($totalSelisih > 0) Pelanggan Nombok @elseif($totalSelisih < 0) Toko Rugi/Kembali Uang @else Impas @endif 
                            Rp {{ number_format(abs($totalSelisih), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <button wire:click="prosesRetur" wire:confirm="Pastikan fisik barang dan uang selisih sudah diterima/dikembalikan. Proses sekarang?" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg shadow text-lg">
                KONFIRMASI & PROSES RETUR
            </button>
        @endif
    @endif
</div>