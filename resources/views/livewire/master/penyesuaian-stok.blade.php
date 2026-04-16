<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow mt-10">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-red-600 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Audit & Penyesuaian Stok Fisik
        </h2>
        <p class="text-gray-500 text-sm mt-1">Gunakan formulir ini hanya untuk barang rusak, hilang, atau selisih opname. Tindakan ini dicatat secara permanen.</p>
    </div>

    @if(session()->has('sukses')) 
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">{{ session('sukses') }}</div> 
    @endif
    
    @error('sistem') 
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">{{ $message }}</div> 
    @enderror

    <form wire:submit="simpan" class="space-y-6">
        
        <!-- Langkah 1: Cari Barang -->
        <div>
            <label class="block font-semibold mb-1">1. Cari Barang yang Ingin Disesuaikan</label>
            @if(!$produk_terpilih)
                <input type="text" wire:model.live.debounce.300ms="keyword" class="w-full border-gray-300 rounded p-2 border focus:border-red-500" placeholder="Ketik SKU atau Nama Barang...">
                
                <!-- Dropdown Pencarian -->
                @if(count($daftarPencarian) > 0)
                    <ul class="border mt-1 rounded bg-white shadow-lg relative z-10">
                        @foreach($daftarPencarian as $p)
                            <li class="p-3 hover:bg-gray-100 cursor-pointer border-b" wire:click="pilihProduk({{ $p->id_produk }})">
                                <span class="font-bold">{{ $p->nama_produk }}</span> (Stok Sistem: {{ $p->stok_display }} {{ $p->satuan }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="bg-blue-50 border border-blue-200 p-4 rounded flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-blue-800">{{ $produk_terpilih->nama_produk }}</h3>
                        <p class="text-sm text-blue-600">Stok Sistem Saat Ini: <span class="font-bold">{{ $produk_terpilih->stok_display }}</span> {{ $produk_terpilih->satuan }}</p>
                    </div>
                    <button type="button" wire:click="$set('produk_terpilih', null)" class="text-sm bg-white border border-gray-300 px-3 py-1 rounded hover:bg-gray-100">Ganti Barang</button>
                </div>
            @endif
        </div>

        @if($produk_terpilih)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border rounded bg-gray-50">
            <!-- Langkah 2: Detail Penyesuaian -->
            <div>
                <label class="block font-semibold mb-1">Tipe Mutasi</label>
                <select wire:model="tipe_penyesuaian" class="w-full border-gray-300 rounded p-2 border">
                    <option value="KOREKSI_MINUS">Barang Keluar (Stok Hilang/Rusak)</option>
                    <option value="KOREKSI_PLUS">Barang Masuk (Kelebihan Opname)</option>
                </select>
                @error('tipe_penyesuaian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Jumlah</label>
                <div class="flex items-center gap-2">
                    <input type="number" step="0.01" wire:model="jumlah" class="w-full border-gray-300 rounded p-2 border">
                    <span class="text-gray-500">{{ $produk_terpilih->satuan }}</span>
                </div>
                @error('jumlah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Alasan Penyesuaian (Wajib Jelas)</label>
                <textarea wire:model="keterangan" rows="2" class="w-full border-gray-300 rounded p-2 border" placeholder="Contoh: Barang cacat dari pabrik saat kardus dibuka..."></textarea>
                @error('keterangan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Langkah 3: Keamanan -->
        <div class="bg-red-50 border border-red-200 p-4 rounded mt-6">
            <label class="block font-bold text-red-700 mb-2">Otorisasi Admin</label>
            <p class="text-xs text-red-600 mb-2">Masukkan password login Anda untuk mengonfirmasi bahwa Anda bertanggung jawab atas perubahan ini.</p>
            <input type="password" wire:model="password_admin" class="w-full border-red-300 rounded p-2 border focus:ring-red-500 focus:border-red-500" placeholder="Password Anda...">
            @error('password_admin') <span class="text-red-500 font-bold text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded shadow mt-4 transition-colors">
            KONFIRMASI PERUBAHAN STOK
        </button>
        @endif

    </form>
</div>