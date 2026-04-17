<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Master Data Katalog Produk</h2>
        <button wire:click="$set('form_open', true)" class="bg-blue-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-blue-700 transition">
            + Tambah Produk SKU Baru
        </button>
    </div>

    @if(session()->has('sukses'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">{{ session('sukses') }}</div>
    @endif

    <!-- FORM TAMBAH / EDIT PRODUK -->
    @if($form_open)
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8 border-t-4 border-blue-500">
            <h3 class="font-bold text-xl mb-4 border-b pb-2">{{ $edit_id ? 'Edit Data Produk' : 'Buat Produk Baru' }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Data Dasar (Sebelah Kiri) -->
                <div class="space-y-4">
                    <h4 class="font-bold text-gray-700 bg-gray-100 p-2 rounded">Informasi Dasar</h4>
                    
                    <div>
                        <label class="block text-sm font-bold mb-1">Kategori Produk *</label>
                        <select wire:model.live="id_kategori" class="w-full border-gray-300 rounded p-2 border focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($daftarKategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        @error('id_kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">Kode Barang / SKU *</label>
                        <input type="text" wire:model="kode_barang" placeholder="Contoh: FR-12-CN-GRS" class="w-full border-gray-300 rounded p-2 border focus:ring-blue-500 uppercase">
                        @error('kode_barang') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">Nama Produk Kasir *</label>
                        <input type="text" wire:model="nama_produk" placeholder="Contoh: Fr 12 in CN Grs (3)" class="w-full border-gray-300 rounded p-2 border focus:ring-blue-500">
                        @error('nama_produk') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Satuan</label>
                            <select wire:model="satuan" class="w-full border-gray-300 rounded p-2 border">
                                <option value="pcs">Pcs / Unit</option>
                                <option value="meter">Meter</option>
                                <option value="kg">Kilogram (Kg)</option>
                                <option value="rol">Rol</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Harga Jual (Rp)</label>
                            <input type="number" wire:model="harga_jual_satuan" class="w-full border-gray-300 rounded p-2 border">
                            @error('harga_jual_satuan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" wire:model="lacak_stok" class="w-5 h-5 text-blue-600 rounded">
                            <span class="font-bold text-gray-700">Lacak Stok Fisik Sistem</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Jika centang dihilangkan, produk dianggap unlimited (seperti Jasa/Ongkos Kirim).</p>
                    </div>
                </div>

                <!-- Spesifikasi Dinamis (Sebelah Kanan) -->
                <div class="space-y-4">
                    <h4 class="font-bold text-blue-700 bg-blue-50 p-2 rounded">Spesifikasi Detail (Sesuai Kategori)</h4>
                    
                    @if(!$id_kategori)
                        <div class="text-gray-400 text-sm text-center py-10 border-2 border-dashed rounded">
                            Pilih Kategori Produk terlebih dahulu untuk memunculkan form spesifikasi.
                        </div>
                    @elseif(count($atributDinamis) == 0)
                        <div class="text-gray-500 text-sm p-4 bg-gray-50 border rounded">
                            Kategori ini tidak memiliki spesifikasi tambahan.
                        </div>
                    @else
                        <!-- INI ADALAH BAGIAN SMART UI NYA -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($atributDinamis as $attr)
                                <div>
                                    <label class="block text-sm font-bold text-blue-800 mb-1">{{ $attr->nama_atribut }}</label>
                                    <select wire:model="metadata_input.{{ $attr->nama_atribut }}" class="w-full border-blue-200 rounded p-2 border bg-blue-50 focus:ring-blue-500">
                                        <option value="">-- Pilih {{ $attr->nama_atribut }} --</option>
                                        <!-- Pilihan opsi berasal dari Master Atribut JSON di database -->
                                        @foreach($attr->pilihan_opsi as $opsi)
                                            <option value="{{ $opsi }}">{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                    @error('metadata_input.' . $attr->nama_atribut) <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!$edit_id)
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800 font-bold">⚠️ Catatan Pengisian Stok:</p>
                            <p class="text-xs text-yellow-700 mt-1">Anda tidak dapat mengisi stok saat membuat produk baru demi keamanan data. Setelah produk tersimpan, masuk ke menu <b>Adjust Stok</b> untuk memasukkan jumlah fisik barang masuk.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex gap-3 border-t pt-4">
                <button wire:click="simpan" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded font-black shadow transition">SIMPAN PRODUK</button>
                <button wire:click="resetForm" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded font-bold transition">BATAL</button>
            </div>
        </div>
    @endif

    <!-- TABEL DATA PRODUK -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <input type="text" wire:model.live.debounce.500ms="keyword" placeholder="🔍 Cari SKU, Nama Barang, atau Spesifikasi..." class="w-full md:w-1/2 border-gray-300 rounded-lg p-3 border shadow-sm focus:ring-blue-500">
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-sm">
                        <th class="p-3 whitespace-nowrap">SKU / Kode</th>
                        <th class="p-3 min-w-[250px]">Nama Produk & Spesifikasi</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3 text-right">Harga Jual</th>
                        <th class="p-3 text-center">Stok</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($daftarProduk as $prod)
                    <tr class="border-b hover:bg-gray-50 {{ !$prod->status_aktif ? 'bg-red-50 opacity-60' : '' }}">
                        <td class="p-3 font-mono text-xs font-bold text-gray-600">{{ $prod->kode_barang }}</td>
                        <td class="p-3">
                            <p class="font-bold text-gray-800 text-base">{{ $prod->nama_produk }}</p>
                            <!-- Render JSON Metadata as UI Badges -->
                            @if($prod->metadata)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($prod->metadata as $key => $val)
                                        <span class="bg-blue-100 text-blue-800 border border-blue-200 text-[10px] px-2 py-0.5 rounded font-semibold">{{ $key }}: {{ $val }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="p-3 font-semibold text-gray-600">{{ $prod->kategori->nama_kategori }}</td>
                        <td class="p-3 text-right font-bold text-green-700">Rp {{ number_format($prod->harga_jual_satuan, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            @if($prod->lacak_stok)
                                <span class="font-bold {{ $prod->stok_saat_ini <= 0 ? 'text-red-600' : 'text-gray-800' }}">
                                    <!-- stok_display dipanggil untuk membuang desimal jika Pcs -->
                                    {{ $prod->stok_display }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $prod->satuan }}</span>
                            @else
                                <span class="text-xs text-gray-400 font-bold">UNLIMITED</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($prod->status_aktif)
                                <button wire:click="toggleAktif({{ $prod->id_produk }})" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold hover:bg-red-100 hover:text-red-700" title="Klik untuk Nonaktifkan">AKTIF</button>
                            @else
                                <button wire:click="toggleAktif({{ $prod->id_produk }})" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold hover:bg-green-100 hover:text-green-700" title="Klik untuk Aktifkan">NONAKTIF</button>
                            @endif
                        </td>
                        <td class="p-3">
                            <button wire:click="edit({{ $prod->id_produk }})" class="text-blue-600 hover:text-blue-800 hover:underline font-bold">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400 font-bold">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t">
            {{ $daftarProduk->links() }}
        </div>
    </div>
</div>