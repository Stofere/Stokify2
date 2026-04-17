<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Master Data Pelanggan</h2>
        <button wire:click="$set('form_open', true)" class="bg-blue-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-blue-700">
            + Tambah Pelanggan
        </button>
    </div>

    @if(session()->has('sukses'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('sukses') }}</div>
    @endif

    <!-- Form Buka/Tutup -->
    @if($form_open)
        <div class="bg-white p-6 rounded shadow mb-6 border-t-4 border-blue-500">
            <h3 class="font-bold text-lg mb-4">{{ $edit_id ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Nama Lengkap *</label>
                    <input type="text" wire:model="nama" class="w-full border rounded p-2">
                    @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">No. Telepon / WA</label>
                    <input type="text" wire:model="telepon" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Alamat</label>
                    <input type="text" wire:model="alamat" class="w-full border rounded p-2">
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="simpan" class="bg-green-600 text-white px-6 py-2 rounded font-bold">SIMPAN</button>
                <button wire:click="resetForm" class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-bold">BATAL</button>
            </div>
        </div>
    @endif

    <!-- Tabel Data -->
    <div class="bg-white rounded shadow p-4">
        <input type="text" wire:model.live.debounce.300ms="keyword" placeholder="Cari nama pelanggan..." class="w-full border rounded p-2 mb-4">
        
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-sm border-b">
                    <th class="p-3">Nama</th>
                    <th class="p-3">Telepon</th>
                    <th class="p-3">Alamat</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarPelanggan as $plg)
                <tr class="border-b hover:bg-gray-50 {{ !$plg->aktif ? 'opacity-50' : '' }}">
                    <td class="p-3 font-semibold">{{ $plg->nama }}</td>
                    <td class="p-3">{{ $plg->telepon ?? '-' }}</td>
                    <td class="p-3 truncate max-w-xs">{{ $plg->alamat ?? '-' }}</td>
                    <td class="p-3">
                        @if($plg->aktif)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">AKTIF</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">NONAKTIF</span>
                        @endif
                    </td>
                    <td class="p-3 flex gap-2">
                        <button wire:click="edit({{ $plg->id_pelanggan }})" class="text-blue-600 hover:underline text-sm font-bold">Edit</button>
                        <button wire:click="toggleAktif({{ $plg->id_pelanggan }})" class="{{ $plg->aktif ? 'text-red-600' : 'text-green-600' }} hover:underline text-sm font-bold">
                            {{ $plg->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>