@php
    $isOwnerRole = Auth::user()->peran === 'OWNER';
@endphp

<div class="fade-in">

    {{-- ================================================================== --}}
    {{-- ADMIN DASHBOARD: Fantasy-Minimalist Frieren Theme                  --}}
    {{-- ================================================================== --}}
    @if(!$isOwner)

    @php
        $hour = \Carbon\Carbon::now('Asia/Jakarta')->hour;

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Pagi';
            $mainText = 'Sudahkah kamu menginput penjualan hari ini?';
            $frierenJoke = 'Karena Pahlawan Himmel pasti udah ngegas dari subuh buat farming target harian~ 🌅☕';
        } 
        elseif ($hour >= 12 && $hour < 15) {
            $greeting = 'Siang';
            $mainText = 'Semangat terus ya, Master! Sudah makan siang?';
            $frierenJoke = 'Karena bahkan Himmel butuh recharge dulu biar ga burnout grinding sales~ 🍱✨';
        } 
        elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Sore';
            $mainText = 'Sudahkah kamu menginput penjualan hari ini?';
            $frierenJoke = 'Karena di sore hari Himmel malah makin ganas farming, jangan kalah semangatnya ya~ 🔥';
        } 
        else {
            $greeting = 'Malam';
            $mainText = 'Jangan lembur terlalu lama ya~ Istirahat yang cukup!';
            $frierenJoke = 'Karena Pahlawan Himmel juga tau kapan harus stop farming dan tidur, balance life dulu dong~ 🌙😴';
        }
    @endphp

    {{-- Hero Section --}}
    <div class="px-4 md:px-8 pt-6">
        <div class="w-full bg-gradient-to-br from-[#E0F2F1] via-[#F1F5F9] to-[#F8F9FA] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between relative overflow-hidden group">

            {{-- Chibi Frieren --}}
            <div id="frieren-chibi" class="relative w-32 h-32 md:w-48 md:h-48 shrink-0 frieren-float z-10 block order-last md:order-none cursor-pointer">
                <img src="/images/chibi-frieren-open.png" alt="Frieren"
                    class="absolute inset-0 w-full h-full object-contain frieren-wave transition-transform duration-500 group-hover:scale-105 frieren-eye-open">
                <img src="/images/chibi-frieren-blink.png" alt="Frieren Blink"
                    class="absolute inset-0 w-full h-full object-contain frieren-wave transition-transform duration-500 group-hover:scale-105 frieren-eye-closed opacity-0">
            </div>

            {{-- Welcome Text --}}
            <div class="flex-1 md:ml-8 text-center md:text-left z-10 mt-4 md:mt-0">
                <h1 class="font-headline text-2xl md:text-3xl font-bold text-sage-dark tracking-tight flex items-center justify-center md:justify-start gap-2">
                    🌿 Selamat {{ $greeting }}, {{ Auth::user()->name ?? 'Master' }}!
                </h1>
                <p class="text-slate-500 mt-3 text-sm leading-relaxed max-w-xl">
                    {!! $mainText !!}<br>
                    <span class="italic text-sage">{{ $frierenJoke }}</span>
                </p>
                <div class="mt-5">
                    <a href="/pos" class="inline-flex items-center gap-2 bg-gradient-to-r from-sage-dark to-sage text-white font-bold py-2.5 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 text-sm">
                        <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                        Input Transaksi Baru
                    </a>
                </div>
            </div>

            {{-- Decorative orbs --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-sage/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 right-20 w-32 h-32 bg-sage-light/40 rounded-full blur-xl"></div>
        </div>
    </div>

    {{-- Admin Content --}}
    <div class="p-4 md:p-8 space-y-6">

        {{-- Bento Row 1: Metric + Activity --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Nota Count Card --}}
            <a href="/transaksi/riwayat" class="bg-white rounded-2xl p-6 flex items-center gap-4 hover:shadow-md transition-all group cursor-pointer relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gold rounded-r-full"></div>
                <div class="bg-sage-light p-3.5 rounded-xl text-sage-dark group-hover:bg-sage group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400">Nota POS Hari Ini</p>
                    <h3 class="font-headline text-3xl font-bold text-sage-dark mt-1">{{ $notaCount }}</h3>
                </div>
            </a>

            {{-- Chart Preview Card --}}
            <div class="md:col-span-2 bg-white rounded-2xl p-6">
                <h4 class="font-label text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-4">Grafik Transaksi (7 Hari Terakhir)</h4>
                <div class="w-full h-48">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Bento Row 2: Activity Log --}}
        <div class="bg-white rounded-2xl overflow-hidden">
            <h4 class="font-label text-[11px] font-bold uppercase tracking-widest text-slate-400 px-6 pt-5 pb-3">Log Aktivitas Sistem Hari Ini</h4>
            <div class="px-6 pb-5 max-h-[280px] overflow-y-auto">
                @if(count($aktivitasHariIni) == 0)
                    <p class="text-center text-slate-400 text-sm font-semibold py-8">Belum ada aktivitas hari ini.</p>
                @else
                    <div class="space-y-3">
                        @foreach($aktivitasHariIni as $log)
                            <div class="flex gap-3 text-sm items-start">
                                <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 {{ in_array($log->tipe, ['MASUK', 'KOREKSI_PLUS']) ? 'bg-emerald-400' : (in_array($log->tipe, ['KELUAR', 'KOREKSI_MINUS']) ? 'bg-red-400' : 'bg-sage') }}"></div>
                                <div>
                                    <p class="font-semibold text-sage-dark">{{ $log->user->name }}</p>
                                    <p class="text-slate-500 text-xs">
                                        {{ str_replace('_', ' ', $log->tipe) }} <span class="font-bold text-sage-dark">{{ abs($log->jumlah) }}</span> qty pada <span class="text-sage font-semibold">{{ $log->produk->nama_produk }}</span>.
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }} | {{ $log->keterangan }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @endif

    {{-- ================================================================== --}}
    {{-- OWNER DASHBOARD: Professional SaaS Analytical Theme                --}}
    {{-- ================================================================== --}}
    @if($isOwner)

    <div class="p-4 md:p-8 space-y-6">

        {{-- Title Row --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h2 class="font-headline text-2xl md:text-3xl font-bold text-charcoal">Ringkasan Sistem ERP</h2>
                <p class="text-slate-500 text-sm mt-1">Dasbor manajemen — analisis data real-time.</p>
            </div>
            <span class="bg-blue-pro/10 text-blue-pro px-3 py-1.5 rounded-lg text-xs font-bold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
        </div>

        {{-- KPI Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Nota Count --}}
            <a href="/transaksi/riwayat" class="bg-white rounded-2xl p-5 flex items-center gap-4 hover:shadow-md transition-all group cursor-pointer relative overflow-hidden border border-slate-100">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-pro rounded-r-full"></div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-pro group-hover:bg-blue-pro group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400">Total Nota Hari Ini</p>
                    <h3 class="font-headline text-3xl font-bold text-charcoal flex items-baseline gap-2">{{ $notaCount }} <span class="text-xs text-slate-400 font-body font-semibold">transaksi</span></h3>
                </div>
            </a>

            {{-- Omset --}}
            <div class="bg-white rounded-2xl p-5 flex items-center gap-4 border border-slate-100 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500 rounded-r-full"></div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400">Omset Kotor Hari Ini</p>
                    <h3 class="font-headline text-2xl font-bold text-charcoal mt-1">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Retur --}}
            <div class="bg-white rounded-2xl p-5 flex items-center gap-4 border border-slate-100 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500 rounded-r-full"></div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-label font-bold uppercase tracking-widest text-slate-400">Selisih Retur Hari Ini</p>
                    <h3 class="font-headline text-xl font-bold text-charcoal mt-1">Rp {{ number_format($returHariIni, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-semibold">(+) Plg Nombok, (-) Toko Rugi</p>
                </div>
            </div>
        </div>

        {{-- Chart + Activity Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Chart --}}
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100">
                <h4 class="font-label text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-4">Grafik Jumlah Transaksi (7 Hari Terakhir)</h4>
                <div class="w-full h-56">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-white rounded-2xl border border-slate-100 flex flex-col h-full overflow-hidden">
                <h4 class="font-label text-[11px] font-bold uppercase tracking-widest text-slate-400 p-5 pb-3 border-b border-slate-100">Log Aktivitas Hari Ini</h4>
                <div class="flex-1 overflow-y-auto p-5" style="max-height: 280px;">
                    @if(count($aktivitasHariIni) == 0)
                        <p class="text-center text-slate-400 text-sm font-semibold mt-8">Belum ada aktivitas hari ini.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($aktivitasHariIni as $log)
                                <div class="flex gap-3 text-sm items-start">
                                    <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 {{ in_array($log->tipe, ['MASUK', 'KOREKSI_PLUS']) ? 'bg-emerald-400' : (in_array($log->tipe, ['KELUAR', 'KOREKSI_MINUS']) ? 'bg-red-400' : 'bg-blue-400') }}"></div>
                                    <div>
                                        <p class="font-semibold text-charcoal">{{ $log->user->name }}</p>
                                        <p class="text-slate-500 text-xs">
                                            {{ str_replace('_', ' ', $log->tipe) }} <span class="font-bold text-charcoal">{{ abs($log->jumlah) }}</span> qty — <span class="text-blue-pro font-semibold">{{ $log->produk->nama_produk }}</span>
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Performers Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Top Marketing --}}
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <h4 class="font-label text-[11px] font-bold uppercase tracking-widest px-5 pt-5 pb-3 text-slate-400 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-amber-500">emoji_events</span>
                    Performa Marketing (Bulan Ini)
                </h4>
                <div class="divide-y divide-slate-50">
                    @forelse($topMarketing as $index => $mkt)
                        <div class="px-5 py-3 flex justify-between items-center hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-blue-pro/10 text-blue-pro flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</div>
                                <p class="font-semibold text-charcoal text-sm">{{ $mkt->marketing->nama }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-headline font-bold text-blue-pro">{{ $mkt->total_transaksi }}</p>
                                <p class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">Kali Jualan</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-slate-400 text-sm font-semibold">Belum ada data marketing bulan ini.</div>
                    @endforelse
                </div>
            </div>

            {{-- Top Pelanggan --}}
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <h4 class="font-label text-[11px] font-bold uppercase tracking-widest px-5 pt-5 pb-3 text-slate-400 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-rose-400">favorite</span>
                    Pelanggan Paling Aktif (Bulan Ini)
                </h4>
                <div class="divide-y divide-slate-50">
                    @forelse($topPelanggan as $index => $plg)
                        <div class="px-5 py-3 flex justify-between items-center hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</div>
                                <p class="font-semibold text-charcoal text-sm">{{ $plg->pelanggan->nama }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-headline font-bold text-rose-500">{{ $plg->total_transaksi }}</p>
                                <p class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">Kali Beli</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-slate-400 text-sm font-semibold">Belum ada data pelanggan tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ============================================== --}}
{{-- ANIMATIONS & CHART SCRIPTS                     --}}
{{-- ============================================== --}}
<style>
    @keyframes floating { 0% { transform: translateY(0px); } 50% { transform: translateY(-8px); } 100% { transform: translateY(0px); } }
    .frieren-float { animation: floating 4s ease-in-out infinite; }
    @keyframes wave { 0% { transform: rotate(0deg); } 25% { transform: rotate(2deg); } 75% { transform: rotate(-2deg); } 100% { transform: rotate(0deg); } }
    .frieren-wave { animation: wave 6s ease-in-out infinite; }
    .frieren-eye-closed { opacity: 0; transition: opacity 0.1s ease-in-out; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        const ctx = document.getElementById('salesChart');
        if(!ctx) return;

        const labels = @json($chartLabels7Hari);
        const dataPoint = @json($chartData7Hari);
        const isOwner = {{ $isOwner ? 'true' : 'false' }};

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: dataPoint,
                    borderColor: isOwner ? '#1E3A8A' : '#84A59D',
                    backgroundColor: isOwner ? 'rgba(30, 58, 138, 0.08)' : 'rgba(132, 165, 157, 0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: isOwner ? '#1E3A8A' : '#84A59D',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Frieren blink
        const chibi = document.getElementById('frieren-chibi');
        if (chibi) {
            const openImg = chibi.querySelector('.frieren-eye-open');
            const closedImg = chibi.querySelector('.frieren-eye-closed');
            chibi.addEventListener('click', () => {
                closedImg.style.opacity = '1';
                setTimeout(() => { closedImg.style.opacity = '0'; }, 400);
            });
        }
    });
</script>
