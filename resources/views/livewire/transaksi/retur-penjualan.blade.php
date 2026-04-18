<div>
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Proses Retur Transaksi</h2>

    @if (session()->has('sukses'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded shadow animate-bounce">
            <p class="font-bold">SUKSES!</p>
            <p>{{ session('sukses') }}</p>
        </div>
    @endif

    <!-- TAMPILAN 1: DAFTAR NOTA PENCARIAN (Muncul kalau belum ada nota yang dipilih) -->
    @if(!$notaTerpilih)
        <div class="bg-white rounded-lg shadow border-t-4 border-indigo-500 overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 flex flex-wrap gap-4 items-end border-b">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Mulai Tgl</label>
                    <input wire:model.live="filter_tanggal_mulai" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Sampai Tgl</label>
                    <input wire:model.live="filter_tanggal_akhir" type="date" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cari Nota / Nama Pelanggan</label>
                    <input wire:model.live.debounce.300ms="filter_keyword" type="text" placeholder="Ketik kata kunci..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                            <th class="px-5 py-3 font-bold">Tgl Transaksi</th>
                            <th class="px-5 py-3 font-bold">Kode Nota</th>
                            <th class="px-5 py-3 font-bold">Pelanggan</th>
                            <th class="px-5 py-3 font-bold text-right">Total Transaksi</th>
                            <th class="px-5 py-3 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftar_nota as $nota)
                            <tr class="border-b hover:bg-indigo-50">
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $nota->tanggal_transaksi->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-3 text-sm font-bold text-indigo-700">{{ $nota->kode_nota }}</td>
                                <td class="px-5 py-3 text-sm text-gray-800">{{ $nota->pelanggan->nama ?? 'Umum' }}</td>
                                <td class="px-5 py-3 text-sm text-right font-bold text-gray-600">Rp {{ number_format($nota->total_harga, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-sm text-center">
                                    <button wire:click="pilihNota({{ $nota->id_transaksi_penjualan }})" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-4 rounded shadow transition-colors">
                                        Pilih Nota
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500 font-bold">Tidak ada nota ditemukan di tanggal tersebut.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAMPILAN 2: DETAIL NOTA YANG DIPILIH -->
    @if($notaTerpilih)
        <div class="mb-4">
            <button wire:click="batalPilihNota" class="text-indigo-600 font-bold hover:text-indigo-800 bg-white px-4 py-2 rounded-lg shadow-sm border">&larr; Kembali ke Daftar Nota</button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-800 text-white px-6 py-4 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h3 class="text-xl font-bold">Detail Nota: {{ $notaTerpilih->kode_nota }}</h3>
                    <p class="text-sm text-gray-300">Tgl: {{ $notaTerpilih->tanggal_transaksi->format('d M Y, H:i') }} | Kasir: {{ $notaTerpilih->user->name ?? 'Admin' }}</p>
                </div>
                <div class="text-left md:text-right mt-2 md:mt-0 bg-gray-700 px-4 py-2 rounded-lg">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Pelanggan</p>
                    <p class="text-lg font-bold">{{ $notaTerpilih->pelanggan->nama ?? 'Pelanggan Umum Walk-in' }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                            <th class="px-5 py-3 font-bold">Nama Barang</th>
                            <th class="px-5 py-3 font-bold text-center">Beli</th>
                            <th class="px-5 py-3 font-bold text-center text-red-600">Diretur</th>
                            <th class="px-5 py-3 font-bold text-center text-green-600">Sisa</th>
                            <th class="px-5 py-3 font-bold text-right">Harga Satuan</th>
                            <th class="px-5 py-3 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notaTerpilih->detailPenjualan as $detail)
                            @php
                                $sisaBisaDiretur = $detail->jumlah - $detail->jumlah_diretur;
                            @endphp
                            <tr class="border-b hover:bg-gray-50 {{ $sisaBisaDiretur <= 0 ? 'bg-gray-50 opacity-50' : '' }}">
                                <td class="px-5 py-4 text-sm font-bold text-gray-800">
                                    {{ $detail->produk->nama_produk }}
                                </td>
                                <td class="px-5 py-4 text-sm text-center font-bold text-gray-600">
                                    {{ fmod($detail->jumlah, 1) == 0 ? (int)$detail->jumlah : $detail->jumlah }}
                                </td>
                                <td class="px-5 py-4 text-sm text-center font-bold text-red-600">
                                    {{ fmod($detail->jumlah_diretur, 1) == 0 ? (int)$detail->jumlah_diretur : $detail->jumlah_diretur }}
                                </td>
                                <td class="px-5 py-4 text-sm text-center font-bold text-green-600">
                                    {{ fmod($sisaBisaDiretur, 1) == 0 ? (int)$sisaBisaDiretur : $sisaBisaDiretur }}
                                </td>
                                <td class="px-5 py-4 text-sm text-right font-semibold">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-sm text-center">
                                    @if($sisaBisaDiretur > 0)
                                        <button wire:click="bukaModalRetur({{ $detail->id_detail_penjualan }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-4 rounded-lg shadow transition">
                                            &#8644; Proses Retur
                                        </button>
                                    @else
                                        <span class="text-xs font-bold text-gray-400 bg-gray-200 px-2 py-1 rounded">Habis Diretur</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MODAL PROSES RETUR YANG DIPERBARUI (Dengan Live Search Barang) -->
    @if($showReturModal && $detailTerpilih && $produk_pengganti)
        @php
            $sisaMaksimal = $detailTerpilih->jumlah - $detailTerpilih->jumlah_diretur;
            $selisihSatuan = $produk_pengganti->harga_jual_satuan - $detailTerpilih->harga_satuan;
            $totalSelisih = $selisihSatuan * (float)($qty_retur ?: 0);
        @endphp
        
        <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl flex flex-col md:flex-row overflow-hidden border-t-4 border-yellow-500 relative">
                
                <!-- Tombol Close Modal (X) -->
                <button wire:click="tutupModalRetur" class="absolute top-2 right-4 text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>

                <!-- KIRI: INFORMASI BARANG LAMA -->
                <div class="w-full md:w-1/2 bg-gray-50 p-6 border-r border-gray-200">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 border-b border-gray-200 pb-2">Barang Yang Dikembalikan Pelanggan</h3>
                    
                    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xl font-bold text-gray-800 leading-tight">{{ $detailTerpilih->produk->nama_produk }}</p>
                        <p class="text-sm text-gray-500 mt-1 font-semibold">Harga di Nota: <span class="text-blue-600">Rp {{ number_format($detailTerpilih->harga_satuan, 0, ',', '.') }}</span></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Diretur</label>
                            <input wire:model.live="qty_retur" type="number" min="0.01" max="{{ $sisaMaksimal }}" step="0.01" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-white font-bold focus:ring-yellow-500 text-lg">
                            <p class="text-[11px] text-red-500 mt-1 font-bold">Batas Maksimal: {{ fmod($sisaMaksimal, 1) == 0 ? (int)$sisaMaksimal : $sisaMaksimal }}</p>
                            @error('qty_retur') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kondisi Fisik Barang</label>
                            <select wire:model="kondisi_retur" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-white font-bold focus:ring-yellow-500">
                                <option value="BAGUS" class="text-green-600">BAGUS (Kembali Gudang)</option>
                                <option value="RUSAK" class="text-red-600">RUSAK (Dibuang/Cacat)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- KANAN: BARANG PENGGANTI & UANG -->
                <div class="w-full md:w-1/2 p-6 flex flex-col justify-between bg-white">
                    <div>
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 border-b border-gray-200 pb-2">Barang Pengganti (Baru)</h3>
                        
                        <!-- LIVE SEARCH BARANG PENGGANTI -->
                        <div class="mb-5 relative">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Ganti dengan barang lain? (Ketik untuk mencari)</label>
                            <input wire:model.live.debounce.300ms="search_produk_pengganti" type="text" placeholder="🔍 Ketik SKU, Nama, atau Merk Barang..." class="w-full border border-blue-300 rounded-lg px-3 py-2.5 text-sm focus:ring-blue-500 bg-blue-50 shadow-inner">
                            
                            <!-- Dropdown Hasil Pencarian -->
                            @if(count($hasil_pencarian_produk) > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                    @foreach($hasil_pencarian_produk as $hasil)
                                        <div wire:click="pilihBarangPengganti({{ $hasil->id_produk }})" class="p-3 border-b cursor-pointer hover:bg-blue-100 flex justify-between items-center transition">
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ $hasil->nama_produk }}</p>
                                                <p class="text-xs text-green-700 font-bold">Rp {{ number_format($hasil->harga_jual_satuan, 0, ',', '.') }}</p>
                                            </div>
                                            <span class="text-xs font-bold px-2 py-1 rounded {{ $hasil->stok_saat_ini > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">Sisa: {{ $hasil->stok_display }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Info Barang Pengganti Terpilih -->
                        <div class="bg-gray-100 border border-gray-300 p-4 rounded-lg mb-5 flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Akan Diberikan ke Pelanggan:</p>
                                <p class="text-lg font-bold text-blue-700 leading-tight">{{ $produk_pengganti->nama_produk }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-black text-gray-800">Rp {{ number_format($produk_pengganti->harga_jual_satuan, 0, ',', '.') }}</p>
                                <span class="text-[10px] text-gray-500 font-bold uppercase">Harga Satuan</span>
                            </div>
                        </div>

                        <!-- PERHITUNGAN UANG OTOMATIS -->
                        <div class="mb-5 p-4 rounded-lg shadow-inner {{ $totalSelisih > 0 ? 'bg-orange-50 border-2 border-orange-300' : ($totalSelisih < 0 ? 'bg-green-50 border-2 border-green-300' : 'bg-gray-50 border-2 border-gray-300') }}">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1">Status Keuangan (Selisih Biaya)</p>
                            @if($totalSelisih > 0)
                                <p class="text-xl font-black text-orange-700">Pelanggan Nambah: Rp {{ number_format(abs($totalSelisih), 0, ',', '.') }}</p>
                            @elseif($totalSelisih < 0)
                                <p class="text-xl font-black text-green-700">Toko Kembalikan: Rp {{ number_format(abs($totalSelisih), 0, ',', '.') }}</p>
                            @else
                                <p class="text-xl font-black text-gray-700">Tukar Guling (Rp 0 / Pas)</p>
                            @endif
                        </div>
                    </div>

                    <!-- EKSEKUSI & PASSWORD -->
                    <form wire:submit="prosesRetur" class="mt-auto border-t border-gray-200 pt-4">
                        <div class="mb-3">
                            <input wire:model="catatan" type="text" placeholder="Catatan Wajib (Cth: Speaker sobek minta tukar)" class="w-full border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 focus:ring-yellow-500" required>
                        </div>
                        <div class="mb-4">
                            <input wire:model="password_admin" type="password" placeholder="Otorisasi: Masukkan Password Admin" class="w-full border-red-300 rounded-lg px-3 py-2.5 text-sm focus:ring-red-500" required>
                            @error('password_admin') <span class="text-red-600 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" wire:click="tutupModalRetur" class="bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded-lg transition">Batal</button>
                            <button type="submit" wire:confirm="Pastikan fisik barang lama dan uang selisih sudah diterima/dikembalikan. Lanjutkan proses?" class="bg-yellow-500 hover:bg-yellow-600 text-white font-black py-2.5 px-6 rounded-lg shadow-lg transition">
                                PROSES RETUR SEKARANG
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    @endif
</div>