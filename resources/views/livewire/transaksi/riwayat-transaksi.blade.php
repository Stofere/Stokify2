@php $isOwnerRole = Auth::user()->peran === 'OWNER'; @endphp

<div class="p-4 md:p-8 max-w-7xl mx-auto fade-in">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-3">
        <div>
            <h2 class="font-headline text-2xl md:text-3xl font-bold {{ $isOwnerRole ? 'text-charcoal' : 'text-sage-dark' }}">Riwayat Dokumen Transaksi</h2>
            <p class="text-slate-400 text-sm mt-1">Lacak seluruh riwayat penjualan kasir dan proses tukar retur barang.</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-5 {{ $isOwnerRole ? 'border-b border-slate-200' : 'border-b border-sage/15' }}">
        <button wire:click="switchTab('POS')"
                class="px-5 py-3 font-bold text-sm transition-all border-b-2 {{ $activeTab === 'POS'
                    ? ($isOwnerRole ? 'border-blue-pro text-blue-pro' : 'border-sage-dark text-sage-dark')
                    : 'border-transparent text-slate-400 hover:text-slate-600' }}">
            <span class="material-symbols-outlined text-[16px] align-middle mr-1">receipt_long</span>
            Nota Penjualan
        </button>
        <button wire:click="switchTab('RETUR')"
                class="px-5 py-3 font-bold text-sm transition-all border-b-2 {{ $activeTab === 'RETUR'
                    ? ($isOwnerRole ? 'border-violet-600 text-violet-700' : 'border-violet-500 text-violet-600')
                    : 'border-transparent text-slate-400 hover:text-slate-600' }}">
            <span class="material-symbols-outlined text-[16px] align-middle mr-1">swap_horiz</span>
            Nota Retur & Tukar
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end {{ $isOwnerRole ? 'border border-slate-200' : '' }}">
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dari Tgl</label>
            <input wire:model.live="tgl_mulai" type="date" class="border-0 rounded-lg px-3 py-2 text-sm bg-slate-50 focus:ring-2 {{ $isOwnerRole ? 'focus:ring-blue-pro/20' : 'focus:ring-sage/20' }}">
        </div>
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sampai Tgl</label>
            <input wire:model.live="tgl_akhir" type="date" class="border-0 rounded-lg px-3 py-2 text-sm bg-slate-50 focus:ring-2 {{ $isOwnerRole ? 'focus:ring-blue-pro/20' : 'focus:ring-sage/20' }}">
        </div>
        <div class="flex-1">
            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pencarian</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input wire:model.live.debounce.500ms="keyword" type="text" placeholder="Cari Nota, Pelanggan, Sales..."
                       class="w-full pl-10 pr-4 py-2 border-0 rounded-lg text-sm bg-slate-50 focus:ring-2 {{ $isOwnerRole ? 'focus:ring-blue-pro/20' : 'focus:ring-sage/20' }}">
            </div>
        </div>
    </div>

    {{-- TAB: POS --}}
    @if($activeTab === 'POS')
        <div class="bg-white rounded-2xl overflow-hidden fade-in {{ $isOwnerRole ? 'border border-slate-200' : '' }}">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400 {{ $isOwnerRole ? 'border-b border-slate-100' : 'border-b border-sage/10' }}">
                            <th class="p-4">Waktu & Nota</th><th class="p-4">Pelanggan & Sales</th><th class="p-4">Kasir</th><th class="p-4 text-right">Total</th><th class="p-4 text-center">Menu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftarPos as $pos)
                            <tr class="transition-colors {{ $isOwnerRole ? 'hover:bg-slate-50 border-b border-slate-50' : 'hover:bg-sage-light/20 border-b border-sage/5' }}">
                                <td class="p-4">
                                    <p class="text-[10px] text-slate-400 font-semibold">{{ $pos->tanggal_transaksi->format('d/m/Y H:i') }}</p>
                                    <p class="font-headline font-bold mt-0.5 {{ $isOwnerRole ? 'text-blue-pro' : 'text-sage-dark' }}">{{ $pos->kode_nota }}</p>
                                    {{-- FIX: Tampilkan penanda HANYA jika ada status DIRETUR --}}
                                    @if($pos->status_penjualan === 'DIRETUR')
                                        <span class="bg-orange-100 text-orange-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase mt-1 inline-block border border-orange-200">⚠️ Ada Retur</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <p class="font-semibold text-slate-700">{{ $pos->pelanggan->nama ?? 'Walk-in (Umum)' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Sales: {{ $pos->marketing->nama ?? '-' }}</p>
                                </td>
                                <td class="p-4 text-slate-600 font-semibold">{{ $pos->user->name ?? '-' }}</td>
                                <td class="p-4 text-right font-headline font-bold text-emerald-600">Rp {{ number_format($pos->total_harga, 0, ',', '.') }}</td>
                                <td class="p-4 text-center">
                                    <button wire:click="lihatDetail({{ $pos->id_transaksi_penjualan }})"
                                            class="text-[11px] font-bold px-3 py-1.5 rounded-lg transition-colors {{ $isOwnerRole ? 'bg-blue-50 text-blue-pro hover:bg-blue-pro hover:text-white' : 'bg-sage-light text-sage-dark hover:bg-sage hover:text-white' }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-400 font-semibold">Tidak ada Nota Penjualan ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 {{ $isOwnerRole ? 'bg-slate-50 border-t border-slate-100' : 'bg-[#F8F9FA] border-t border-sage/10' }}">{{ $daftarPos->links() }}</div>
        </div>
    @endif

    {{-- TAB: RETUR --}}
    @if($activeTab === 'RETUR')
        <div class="bg-white rounded-2xl overflow-hidden fade-in {{ $isOwnerRole ? 'border border-slate-200' : '' }}">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400 {{ $isOwnerRole ? 'border-b border-slate-100' : 'border-b border-sage/10' }}">
                            <th class="p-4">Waktu & Retur</th><th class="p-4">Nota Asal & Pelanggan</th><th class="p-4">Diinput Oleh</th><th class="p-4 text-right">Selisih</th><th class="p-4 text-center">Menu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftarRetur as $retur)
                            <tr class="transition-colors {{ $isOwnerRole ? 'hover:bg-slate-50 border-b border-slate-50' : 'hover:bg-sage-light/20 border-b border-sage/5' }}">
                                <td class="p-4">
                                    <p class="text-[10px] text-slate-400 font-semibold">{{ $retur->tanggal_retur->format('d/m/Y H:i') }}</p>
                                    <p class="font-headline font-bold mt-0.5 text-violet-600">{{ $retur->kode_retur }}</p>
                                </td>
                                <td class="p-4">
                                    <p class="text-xs text-slate-400 font-semibold mb-0.5">Nota: {{ $retur->transaksiPenjualan->kode_nota ?? '-' }}</p>
                                    <p class="font-semibold text-slate-700">{{ $retur->transaksiPenjualan->pelanggan->nama ?? 'Umum' }}</p>
                                </td>
                                <td class="p-4 text-slate-600 font-semibold">{{ $retur->user->name ?? '-' }}</td>
                                <td class="p-4 text-right">
                                    @if($retur->total_biaya_retur > 0)
                                        <p class="font-bold text-amber-600">+ Rp {{ number_format(abs($retur->total_biaya_retur), 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 uppercase font-bold">Plg Nambah</p>
                                    @elseif($retur->total_biaya_retur < 0)
                                        <p class="font-bold text-emerald-600">- Rp {{ number_format(abs($retur->total_biaya_retur), 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 uppercase font-bold">Toko Kembalikan</p>
                                    @else
                                        <p class="font-bold text-slate-500">Rp 0</p>
                                        <p class="text-[9px] text-slate-400 uppercase font-bold">Tukar Guling</p>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <button wire:click="lihatDetail({{ $retur->id_retur }})"
                                            class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white transition-colors">
                                        Rincian
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-400 font-semibold">Tidak ada Nota Retur ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 {{ $isOwnerRole ? 'bg-slate-50 border-t border-slate-100' : 'bg-[#F8F9FA] border-t border-sage/10' }}">{{ $daftarRetur->links() }}</div>
        </div>
    @endif

    {{-- MODAL DETAIL --}}
    @if($modal_open && $detail_nota)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh] shadow-2xl">
                <div class="px-6 py-4 flex justify-between items-center shrink-0 {{ $isOwnerRole ? 'bg-charcoal text-white' : 'bg-sage-dark text-white' }}">
                    <div>
                        <h3 class="font-headline text-lg font-bold">
                            @if($activeTab == 'POS') Detail Nota Penjualan @else Detail Nota Retur @endif
                        </h3>
                        <p class="text-sm opacity-80 mt-0.5">Kode: {{ $detail_nota->kode_nota ?? $detail_nota->kode_retur }}</p>
                    </div>
                    <button wire:click="tutupModal" class="px-4 py-2 rounded-lg font-bold text-sm bg-white/10 hover:bg-red-500 transition-colors">× Tutup</button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    @if($activeTab == 'POS')
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                            <div class="bg-slate-50 p-3 rounded-lg"><p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Waktu</p><p class="font-semibold text-sm text-slate-700 mt-1">{{ $detail_nota->tanggal_transaksi->format('d M Y, H:i') }}</p></div>
                            <div class="bg-slate-50 p-3 rounded-lg"><p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Kasir</p><p class="font-semibold text-sm text-slate-700 mt-1">{{ $detail_nota->user->name ?? '-' }}</p></div>
                            <div class="bg-slate-50 p-3 rounded-lg"><p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</p><p class="font-semibold text-sm {{ $isOwnerRole ? 'text-blue-pro' : 'text-sage-dark' }} mt-1">{{ $detail_nota->pelanggan->nama ?? 'Umum' }}</p></div>
                            <div class="bg-slate-50 p-3 rounded-lg"><p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Marketing</p><p class="font-semibold text-sm text-slate-700 mt-1">{{ $detail_nota->marketing->nama ?? '-' }}</p></div>
                        </div>
                        <h4 class="font-semibold text-sm text-slate-700 mb-2 border-b pb-2 border-slate-100">Daftar Barang</h4>
                        <table class="w-full text-left text-sm">
                            <thead><tr class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100"><th class="p-3">Barang</th><th class="p-3 text-center">Qty</th><th class="p-3 text-right">Harga</th><th class="p-3 text-right">Subtotal</th></tr></thead>
                            <tbody class="divide-y">
                                @foreach($detail_nota->detailPenjualan as $det)
                                    <tr>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-800 block">{{ $det->produk->nama_produk }}</span>
                                            
                                            {{-- FIX: JEJAK MULTI-RETUR (Mencari semua retur untuk barang ini) --}}
                                            @if($det->jumlah_diretur > 0)
                                                @php
                                                    $daftarJejakRetur = [];
                                                    foreach($detail_nota->transaksiRetur as $retur) {
                                                        foreach($retur->detailRetur as $dRet) {
                                                            if($dRet->id_produk_dikembalikan === $det->id_produk) {
                                                                // Simpan data retur beserta sampul notanya untuk diambil catatannya
                                                                $daftarJejakRetur[] = [
                                                                    'detail' => $dRet,
                                                                    'nota_retur' => $retur
                                                                ];
                                                            }
                                                        }
                                                    }
                                                @endphp

                                                @forelse($daftarJejakRetur as $jejak)
                                                    <div class="mt-2 bg-orange-50 border border-orange-200 rounded p-3 text-xs shadow-sm relative">
                                                        <span class="text-orange-700 font-black block mb-1 uppercase tracking-wider text-[10px]">⚠️ Diretur: {{ $jejak['nota_retur']->tanggal_retur->format('d/m/Y H:i') }}</span>
                                                        <span class="text-gray-700 block mb-1">Dikembalikan <strong class="text-red-600">{{ fmod($jejak['detail']->jumlah, 1) == 0 ? (int)$jejak['detail']->jumlah : $jejak['detail']->jumlah }} qty</strong> (Kondisi: {{ $jejak['detail']->kondisi_barang_dikembalikan }})</span>
                                                        <span class="text-gray-700 block mb-1">Diganti dgn: <strong class="text-green-700">{{ $jejak['detail']->produkPengganti->nama_produk }}</strong></span>
                                                        
                                                        {{-- Catatan Retur --}}
                                                        <span class="block bg-white p-1.5 rounded border border-orange-100 text-gray-600 italic mt-1.5">
                                                            "{{ $jejak['nota_retur']->catatan ?? 'Tanpa catatan' }}"
                                                        </span>
                                                        
                                                        <button wire:click="lihatDetail({{ $jejak['nota_retur']->id_retur }}, 'RETUR')" class="mt-2 bg-white border border-orange-300 text-orange-700 hover:bg-orange-100 px-3 py-1 rounded-full font-bold transition-colors w-max text-[10px]">
                                                            Buka Dokumen Retur &rarr;
                                                        </button>
                                                    </div>
                                                @empty
                                                    {{-- Fallback jika data terputus di database --}}
                                                    <span class="block text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-bold mt-1 w-max border border-red-200">Total Diretur: {{ fmod($det->jumlah_diretur, 1) == 0 ? (int)$det->jumlah_diretur : $det->jumlah_diretur }} qty</span>
                                                @endforelse
                                            @endif
                                        </td>
                                        <td class="p-3 text-center align-top pt-4">
                                            <span class="font-bold text-gray-700">{{ fmod($det->jumlah, 1) == 0 ? (int)$det->jumlah : $det->jumlah }} {{ $det->satuan_saat_jual }}</span>
                                        </td>
                                        <td class="p-3 text-right text-gray-600 align-top pt-4">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-bold text-green-700 align-top pt-4">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="bg-slate-50"><td colspan="3" class="p-3 text-right font-bold text-slate-500 uppercase text-xs">Total:</td><td class="p-3 text-right font-headline font-bold text-lg text-emerald-600">Rp {{ number_format($detail_nota->total_harga, 0, ',', '.') }}</td></tr></tfoot>
                        </table>
                    @else
                        <div class="mb-5 bg-slate-50 p-4 rounded-lg">
                            <p class="text-xs font-bold text-slate-500 mb-1">Catatan Retur:</p>
                            <p class="text-slate-600 italic">"{{ $detail_nota->catatan ?? 'Tidak ada catatan.' }}"</p>
                        </div>
                        <h4 class="font-semibold text-sm text-slate-700 mb-2 border-b pb-2 border-slate-100">Rincian Tukar</h4>
                        <div class="space-y-3">
                            @foreach($detail_nota->detailRetur as $detRetur)
                                <div class="flex flex-col md:flex-row items-center gap-3 p-3 rounded-xl bg-slate-50">
                                    <div class="flex-1 w-full bg-red-50 p-3 rounded-lg">
                                        <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mb-1">Dikembalikan</p>
                                        <p class="font-bold text-red-700 text-sm">{{ $detRetur->produkDikembalikan->nama_produk }}</p>
                                        <p class="text-xs mt-1 text-slate-600">Qty: {{ fmod($detRetur->jumlah, 1) == 0 ? (int)$detRetur->jumlah : $detRetur->jumlah }} | {{ $detRetur->kondisi_barang_dikembalikan }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300">arrow_forward</span>
                                    <div class="flex-1 w-full bg-emerald-50 p-3 rounded-lg">
                                        <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mb-1">Pengganti</p>
                                        <p class="font-bold text-emerald-700 text-sm">{{ $detRetur->produkPengganti->nama_produk }}</p>
                                        <p class="text-xs mt-1 text-slate-600">Qty: {{ fmod($detRetur->jumlah, 1) == 0 ? (int)$detRetur->jumlah : $detRetur->jumlah }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>