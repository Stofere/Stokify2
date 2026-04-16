<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
    
    <!-- Bagian Kiri: Area Pencarian Produk -->
    <div class="md:col-span-2 bg-white shadow rounded-lg p-4">
        <h2 class="text-xl font-bold mb-4">Katalog Produk</h2>
        
        <!-- Search Bar -->
        <input type="text" wire:model.live.debounce.300ms="keyword" 
            placeholder="Cari SKU, Nama, Merk, Motif..." 
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 mb-6 px-4 py-2 border">

        <!-- Grid Produk -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($daftarProduk as $produk)
                <div class="border rounded-lg p-4 flex flex-col justify-between hover:shadow-lg transition-shadow cursor-pointer"
                     wire:click="tambahKeKeranjang({{ $produk->id_produk }})">
                    
                    <div>
                        <span class="text-xs font-semibold text-gray-500">{{ $produk->kode_barang }}</span>
                        <h3 class="font-bold text-gray-800 leading-tight mt-1">{{ $produk->nama_produk }}</h3>
                        
                        <!-- JSON Metadata Display -->
                        @if($produk->metadata)
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($produk->metadata as $key => $val)
                                    <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-1 rounded">{{ $val }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex justify-between items-center border-t pt-2">
                        <span class="text-blue-600 font-bold">Rp {{ number_format($produk->harga_jual_satuan, 0, ',', '.') }}</span>
                        @if($produk->lacak_stok)
                            <!-- stok_display adalah Accessor yang kita buat di Fase 3 agar pcs tidak ada koma -->
                            <span class="text-sm {{ $produk->stok_saat_ini > 0 ? 'text-green-600' : 'text-red-500 font-bold' }}">
                                Stok: {{ $produk->stok_display }} {{ $produk->satuan }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500">Unlimited</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bagian Kanan: Area Keranjang & Checkout -->
    <div class="bg-white shadow rounded-lg p-4 flex flex-col h-full">
        <h2 class="text-xl font-bold mb-4">Keranjang Belanja</h2>

        <!-- Alert Error dari Exception Service -->
        @error('checkout') <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">{{ $message }}</div> @enderror
        @if(session()->has('error')) <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">{{ session('error') }}</div> @endif
        @if(session()->has('sukses')) <div class="bg-green-100 text-green-700 p-2 rounded mb-4 text-sm">{{ session('sukses') }}</div> @endif

        <!-- Formulir Metadata Nota -->
        <div class="space-y-3 mb-6">
            <select wire:model="id_pelanggan" class="w-full border-gray-300 rounded px-3 py-2 border">
                <option value="">-- Pilih Pelanggan (Opsional) --</option>
                @foreach($daftarPelanggan as $plg)
                    <option value="{{ $plg->id_pelanggan }}">{{ $plg->nama }}</option>
                @endforeach
            </select>

            <select wire:model="id_marketing" class="w-full border-gray-300 rounded px-3 py-2 border">
                <option value="">-- Pilih Sales/Marketing (Opsional) --</option>
                @foreach($daftarMarketing as $mkt)
                    <option value="{{ $mkt->id_marketing }}">{{ $mkt->nama }}</option>
                @endforeach
            </select>
        </div>

        <!-- Daftar Item Keranjang -->
        <div class="flex-1 overflow-y-auto border-t border-b py-4">
            @if(empty($keranjang))
                <div class="text-center text-gray-400 py-10">Keranjang masih kosong</div>
            @else
                <ul class="space-y-4">
                    @foreach($keranjang as $index => $item)
                        <li class="flex justify-between items-center bg-gray-50 p-2 rounded">
                            <div class="w-1/2">
                                <p class="text-sm font-bold truncate">{{ $item['nama_produk'] }}</p>
                                <p class="text-xs text-gray-500">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <!-- Input QTY untuk ganti jumlah manual (Mendukung desimal untuk kabel meteran) -->
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="keranjang.{{ $index }}.jumlah" wire:change="hitungTotal" class="w-16 text-center border-gray-300 rounded text-sm p-1">
                                <span class="text-xs text-gray-500">{{ $item['satuan'] }}</span>
                            </div>

                            <button wire:click="hapusItem({{ $index }})" class="text-red-500 hover:bg-red-100 p-1 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Tombol Bayar -->
        <div class="pt-4 mt-auto">
            <div class="flex justify-between items-center mb-4 text-xl font-bold">
                <span>TOTAL:</span>
                <span class="text-blue-600">Rp {{ number_format($total_belanja, 0, ',', '.') }}</span>
            </div>
            <!-- Make Sure Confirmation (Dari PRD) -->
            <button wire:click="prosesPembayaran" wire:confirm="Pastikan barang bawaan dan nama Marketing sudah sesuai. Cetak Nota sekarang?" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow">
                PROSES & CETAK NOTA
            </button>
        </div>
    </div>
</div>