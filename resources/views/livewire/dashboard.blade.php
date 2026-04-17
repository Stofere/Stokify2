<div class="p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Ringkasan</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Omset Kotor Hari Ini</p>
            <h3 class="text-3xl font-black text-gray-800 mt-2">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Total Transaksi Hari Ini</p>
            <h3 class="text-3xl font-black text-gray-800 mt-2">{{ $notaCount }} <span class="text-lg text-gray-500 font-normal">Nota</span></h3>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Selisih Retur Hari Ini</p>
            <h3 class="text-3xl font-black text-gray-800 mt-2">Rp {{ number_format($returHariIni, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-1">*(+) Pelanggan Nombok, (-) Toko Rugi</p>
        </div>
    </div>
</div>