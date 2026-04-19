<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800">Laporan Penjualan</h2>
            <p class="text-sm text-gray-500">Lihat rekapitulasi penjualan harian, bulanan, atau tahunan.</p>
        </div>
    </div>

    <!-- FILTER AREA -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Laporan</label>
            <select wire:model.live="tipe_filter" class="border-gray-300 rounded-lg p-2.5 text-sm font-bold bg-gray-50 focus:ring-blue-500">
                <option value="harian">Laporan Harian (Rekap WA)</option>
                <option value="bulanan">Laporan Bulanan</option>
                <option value="tahunan">Laporan Tahunan</option>
            </select>
        </div>

        @if($tipe_filter === 'harian')
            <div> 
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tanggal</label>
                <input type="date" wire:model.live="filter_tanggal" class="border-gray-300 rounded-lg p-2.5 text-sm focus:ring-blue-500">
            </div>
        @endif

        @if($tipe_filter === 'bulanan')
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Bulan</label>
                <select wire:model.live="filter_bulan" class="border-gray-300 rounded-lg p-2.5 text-sm focus:ring-blue-500">
                    @for($i=1; $i<=12; $i++) <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option> @endfor
                </select>
            </div>
        @endif

        @if(in_array($tipe_filter, ['bulanan', 'tahunan']))
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tahun</label>
                <input type="number" wire:model.live="filter_tahun" class="border-gray-300 rounded-lg p-2.5 text-sm w-24 focus:ring-blue-500">
            </div>
        @endif

        <div class="ml-auto">
            <button wire:click="cetakPdf" wire:loading.attr="disabled" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-bold shadow flex items-center gap-2">
                <span wire:loading.remove>📄 Cetak Laporan PDF (Detail)</span>
                <span wire:loading>Memproses PDF...</span>
            </button>
        </div>
    </div>

    <!-- TAMPILAN KHUSUS HARIAN: TEXT BOX COPY-PASTE UNTUK BOS -->
    @if($tipe_filter === 'harian')
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-white font-bold flex items-center gap-2">Teks Rekapan Cepat (Untuk Di-Copy ke WA)</h3>
                <span class="text-xs bg-blue-800 text-blue-200 px-3 py-1 rounded-full font-bold">Auto-Generated</span>
            </div>
            
            <textarea readonly rows="8" class="w-full bg-white/10 text-white border border-white/20 rounded-xl p-4 font-mono text-sm focus:ring-0 focus:outline-none" onclick="this.select()">{{ $teks_rekap_harian }}</textarea>
            <p class="text-blue-200 text-xs mt-2 italic">Klik area teks di atas, lalu tekan CTRL+C (Copy) untuk menyalin.</p>
        </div>
    @endif

    <!-- TABEL PREVIEW WEB (DENGAN TOMBOL BUKA MODAL) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 p-4 border-b font-bold text-gray-700">Preview Data Penjualan di Website</div>
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-white border-b-2 border-gray-100 text-gray-500 uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="p-4 font-bold text-center w-12">No</th>
                    <th class="p-4 font-bold">Waktu</th>
                    <th class="p-4 font-bold">Pelanggan & Sales</th>
                    <th class="p-4 font-bold text-right">Total Uang</th>
                    <th class="p-4 font-bold text-center w-40">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $nomor = 1; @endphp
                @forelse($daftarTransaksi as $trx)
                    <tr class="hover:bg-blue-50">
                        <td class="p-4 text-center font-bold text-gray-400">{{ $nomor++ }}.</td>
                        <td class="p-4">
                            <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d/m/Y H:i') }}</p>
                            <p class="text-[10px] text-gray-400 font-mono mt-1">{{ $trx->kode_nota }}</p>
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-blue-700">👤 {{ $trx->pelanggan->nama ?? 'Umum' }}</p>
                            <p class="text-xs text-gray-500 mt-1">👔 {{ $trx->marketing->nama ?? '-' }}</p>
                        </td>
                        <td class="p-4 text-right font-black text-green-700 text-base">
                            Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            <button wire:click="lihatDetail({{ $trx->id_transaksi_penjualan }})" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-lg font-bold text-xs transition-colors shadow-sm">
                                Lihat Rincian
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400 font-bold">Tidak ada transaksi di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ==================================================================== -->
    <!-- MODAL POP-UP BACA DETAIL NOTA (SAMA DENGAN RIWAYAT TRANSAKSI) -->
    <!-- ==================================================================== -->
    @if($modal_open && $detail_nota)
        <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
                
                <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">🛒 Detail Nota Penjualan</h3>
                        <p class="text-gray-300 text-sm mt-1">Kode: <span class="font-bold text-white">{{ $detail_nota->kode_nota }}</span></p>
                    </div>
                    <button wire:click="tutupModal" class="bg-gray-700 hover:bg-red-600 text-white px-4 py-2 rounded font-bold transition">&times; TUTUP</button>
                </div>

                <div class="p-6 overflow-y-auto bg-gray-50 flex-1">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div><p class="text-[10px] font-bold text-gray-500 uppercase">Waktu Transaksi</p><p class="font-bold text-gray-800">{{ $detail_nota->tanggal_transaksi->format('d M Y, H:i') }}</p></div>
                        <div><p class="text-[10px] font-bold text-gray-500 uppercase">Kasir</p><p class="font-bold text-gray-800">{{ $detail_nota->user->name ?? '-' }}</p></div>
                        <div><p class="text-[10px] font-bold text-gray-500 uppercase">Pelanggan</p><p class="font-bold text-blue-700">{{ $detail_nota->pelanggan->nama ?? 'Walk-in (Umum)' }}</p></div>
                        <div><p class="text-[10px] font-bold text-gray-500 uppercase">Marketing</p><p class="font-bold text-gray-800">{{ $detail_nota->marketing->nama ?? '-' }}</p></div>
                    </div>

                    <h4 class="font-bold text-gray-800 mb-2 border-b pb-2">Daftar Barang Terjual</h4>
                    <table class="w-full text-left text-sm bg-white border rounded shadow-sm">
                        <thead class="bg-gray-100 text-gray-600"><tr><th class="p-3">Nama Barang</th><th class="p-3 text-center">Beli</th><th class="p-3 text-right">Harga Satuan</th><th class="p-3 text-right">Subtotal</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach($detail_nota->detailPenjualan as $det)
                                <tr>
                                    <td class="p-3">
                                        <span class="font-bold text-gray-800 block">{{ $det->produk->nama_produk }}</span>
                                        {{-- JEJAK RETUR PINTAR (SMART TRACE) --}}
                                        @if($det->jumlah_diretur > 0)
                                            @php
                                                $jejakRetur = null; $notaReturTerkait = null;
                                                foreach($detail_nota->transaksiRetur as $retur) {
                                                    foreach($retur->detailRetur as $dRet) {
                                                        if($dRet->id_produk_dikembalikan === $det->id_produk) {
                                                            $jejakRetur = $dRet; $notaReturTerkait = $retur; break 2;
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @if($jejakRetur)
                                                <div class="mt-2 bg-orange-50 border border-orange-200 rounded p-2 text-xs">
                                                    <span class="text-orange-700 font-bold block mb-1">⚠️ Telah Diretur (Kondisi: {{ $jejakRetur->kondisi_barang_dikembalikan }})</span>
                                                    <span class="text-gray-600 block">Diganti dgn: <strong class="text-green-700">{{ $jejakRetur->produkPengganti->nama_produk }}</strong> ({{ fmod($jejakRetur->jumlah, 1) == 0 ? (int)$jejakRetur->jumlah : $jejakRetur->jumlah }} qty)</span>
                                                </div>
                                            @else
                                                <span class="block text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-bold mt-1 w-max">Telah Diretur: {{ fmod($det->jumlah_diretur, 1) == 0 ? (int)$det->jumlah_diretur : $det->jumlah_diretur }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="p-3 text-center align-top pt-4">{{ fmod($det->jumlah, 1) == 0 ? (int)$det->jumlah : $det->jumlah }} {{ $det->satuan_saat_jual }}</td>
                                    <td class="p-3 text-right text-gray-600 align-top pt-4">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="p-3 text-right font-bold text-green-700 align-top pt-4">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr><td colspan="3" class="p-3 text-right font-bold uppercase text-gray-600">Total Harga:</td><td class="p-3 text-right font-black text-xl text-green-700">Rp {{ number_format($detail_nota->total_harga, 0, ',', '.') }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>