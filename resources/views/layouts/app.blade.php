<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stokify POS V2 - Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'headline': ['Noto Serif', 'serif'],
                        'body': ['Manrope', 'sans-serif'],
                        'label': ['Manrope', 'sans-serif'],
                    },
                    colors: {
                        'sage':       '#84A59D',
                        'sage-dark':  '#45645E',
                        'sage-light': '#E0F2F1',
                        'sage-bg':    '#F1F3F2',
                        'gold':       '#D4AF37',
                        'gold-light': '#FEF3C7',
                        'slate-pro':  '#475569',
                        'blue-pro':   '#1E3A8A',
                        'charcoal':   '#334155',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Manrope', sans-serif; }
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c8c5; border-radius: 99px; }
    </style>
    @livewireStyles
</head>

@php
    $isOwnerRole = Auth::check() && Auth::user()->peran === 'OWNER';
    $currentRoute = request()->path();
@endphp

<body class="font-body antialiased min-h-screen {{ $isOwnerRole ? 'bg-slate-100' : 'bg-[#F8F9FA]' }}"
      x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    <!-- ============================== -->
    <!-- DESKTOP SIDEBAR                -->
    <!-- ============================== -->
    <aside class="hidden md:flex flex-col fixed left-0 top-0 h-screen z-40 sidebar-transition overflow-hidden
                  {{ $isOwnerRole ? 'bg-charcoal text-slate-300' : 'bg-sage-bg text-sage-dark' }}"
           :class="sidebarOpen ? 'w-64' : 'w-20'">

        <!-- Logo Area -->
        <div class="px-5 py-6 flex items-center gap-3 shrink-0 {{ $isOwnerRole ? 'border-b border-slate-700' : 'border-b border-sage/20' }}">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-headline font-bold text-lg
                        {{ $isOwnerRole ? 'bg-blue-pro text-white' : 'bg-sage text-white' }}">
                S2
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <h1 class="font-headline text-lg font-bold leading-tight {{ $isOwnerRole ? 'text-white' : 'text-sage-dark' }}">Stokify</h1>
                <p class="text-[10px] font-label uppercase tracking-widest {{ $isOwnerRole ? 'text-slate-400' : 'text-sage' }}">
                    {{ $isOwnerRole ? 'Owner Mode' : 'Staff Mode' }}
                </p>
            </div>
        </div>

        <!-- Nav Items -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => '/dashboard', 'match' => 'dashboard'],
                    ['label' => '', 'divider' => true, 'text' => 'Aktivitas Toko'],
                    ['label' => 'Kasir POS', 'icon' => 'point_of_sale', 'href' => '/pos', 'match' => 'pos'],
                    ['label' => 'Retur Barang', 'icon' => 'swap_horiz', 'href' => '/retur', 'match' => 'retur'],
                    ['label' => 'Riwayat Transaksi', 'icon' => 'receipt_long', 'href' => '/transaksi/riwayat', 'match' => 'transaksi'],
                    ['label' => '', 'divider' => true, 'text' => 'Master Data'],
                    ['label' => 'Laporan Penjualan', 'icon' => 'receipt_long', 'href' => '/laporan/penjualan', 'match' => 'laporan/penjualan'],
                    ['label' => 'Laporan Katalog', 'icon' => 'receipt_long', 'href' => '/laporan/katalog', 'match' => 'laporan/katalog'],
                    ['label' => 'Katalog Produk', 'icon' => 'inventory_2', 'href' => '/master/produk', 'match' => 'master/produk'],
                    ['label' => 'Kategori', 'icon' => 'category', 'href' => '/master/kategori', 'match' => 'master/kategori'],
                    ['label' => 'Atribut', 'icon' => 'tune', 'href' => '/master/atribut', 'match' => 'master/atribut'],
                    ['label' => 'Pelanggan', 'icon' => 'group', 'href' => '/master/pelanggan', 'match' => 'master/pelanggan'],
                    ['label' => 'Marketing', 'icon' => 'badge', 'href' => '/master/marketing', 'match' => 'master/marketing'],
                ];
            @endphp

            @foreach($navItems as $item)
                @if(isset($item['divider']))
                    <div class="pt-4 pb-1 px-3" x-show="sidebarOpen" x-transition.opacity>
                        <p class="text-[10px] font-label font-bold uppercase tracking-[0.15em] {{ $isOwnerRole ? 'text-slate-500' : 'text-sage/60' }}">
                            {{ $item['text'] }}
                        </p>
                    </div>
                @else
                    @php $isActive = str_contains($currentRoute, $item['match']); @endphp
                    <a href="{{ $item['href'] }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group relative
                              {{ $isActive
                                  ? ($isOwnerRole
                                      ? 'bg-slate-700/60 text-white'
                                      : 'bg-white/80 text-sage-dark shadow-sm')
                                  : ($isOwnerRole
                                      ? 'text-slate-400 hover:text-white hover:bg-slate-700/40'
                                      : 'text-slate-500 hover:text-sage-dark hover:bg-white/50')
                              }}">
                        @if($isActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-r-full
                                        {{ $isOwnerRole ? 'bg-blue-400' : 'bg-gold' }}"></div>
                        @endif
                        <span class="material-symbols-outlined text-[20px] shrink-0 {{ $isActive ? ($isOwnerRole ? 'text-blue-400' : 'text-gold') : '' }}">{{ $item['icon'] }}</span>
                        <span x-show="sidebarOpen" x-transition.opacity class="text-sm font-semibold truncate">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <!-- Bottom Section -->
        <div class="px-3 py-4 space-y-1 shrink-0 {{ $isOwnerRole ? 'border-t border-slate-700' : 'border-t border-sage/20' }}">
            <!-- Collapse Toggle -->
            <button @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                           {{ $isOwnerRole ? 'text-slate-400 hover:text-white hover:bg-slate-700/40' : 'text-slate-500 hover:text-sage-dark hover:bg-white/50' }}">
                <span class="material-symbols-outlined text-[20px] shrink-0 transition-transform duration-300"
                      :class="sidebarOpen ? '' : 'rotate-180'">chevron_left</span>
                <span x-show="sidebarOpen" x-transition.opacity class="text-sm font-semibold">Tutup Menu</span>
            </button>

            <!-- Logout -->
            <form action="/logout" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                               {{ $isOwnerRole ? 'text-red-400 hover:bg-red-500/10' : 'text-red-500 hover:bg-red-50' }}">
                    <span class="material-symbols-outlined text-[20px] shrink-0">logout</span>
                    <span x-show="sidebarOpen" x-transition.opacity class="text-sm font-semibold">Logout</span>
                </button>
            </form>
        </div>

        <!-- User Badge -->
        <div class="px-4 py-3 shrink-0 {{ $isOwnerRole ? 'bg-slate-800/50' : 'bg-sage/10' }}">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                            {{ $isOwnerRole ? 'bg-blue-pro text-white' : 'bg-sage text-white' }}">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                    <p class="text-xs font-bold truncate {{ $isOwnerRole ? 'text-white' : 'text-sage-dark' }}">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-[10px] uppercase tracking-wider {{ $isOwnerRole ? 'text-slate-500' : 'text-sage/70' }}">{{ Auth::user()->peran ?? 'ADMIN' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============================== -->
    <!-- MAIN CONTENT AREA              -->
    <!-- ============================== -->
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 md:pb-0 pb-20"
         :class="sidebarOpen ? 'md:ml-64' : 'md:ml-20'">

        <!-- Top Header (Desktop) -->
        <header class="hidden md:flex items-center justify-between px-8 h-14 shrink-0
                        {{ $isOwnerRole ? 'bg-white border-b border-slate-200' : 'bg-white/60 backdrop-blur-md border-b border-sage/10' }}">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] {{ $isOwnerRole ? 'text-slate-400' : 'text-sage' }}">location_on</span>
                <span class="text-xs font-label font-semibold {{ $isOwnerRole ? 'text-slate-500' : 'text-sage' }} uppercase tracking-wider">Sistem Internal Enterprise</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-label {{ $isOwnerRole ? 'text-slate-400 bg-slate-100 border border-slate-200' : 'text-sage bg-sage-light/50 border border-sage/20' }} px-3 py-1 rounded-full font-semibold">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d M Y') }}
                </span>
            </div>
        </header>

        <!-- Mobile Header -->
        <header class="md:hidden flex items-center justify-between px-4 h-14 shrink-0
                        {{ $isOwnerRole ? 'bg-charcoal text-white' : 'bg-white border-b border-sage/10' }}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-headline font-bold
                            {{ $isOwnerRole ? 'bg-blue-pro text-white' : 'bg-sage text-white' }}">S2</div>
                <span class="font-headline font-bold {{ $isOwnerRole ? 'text-white' : 'text-sage-dark' }}">Stokify</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold
                            {{ $isOwnerRole ? 'bg-blue-pro text-white' : 'bg-sage text-white' }}">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto relative">
            {{ $slot }}
        </main>
    </div>

    <!-- ============================== -->
    <!-- MOBILE BOTTOM NAV              -->
    <!-- ============================== -->
    <nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 pb-5 pt-2
                {{ $isOwnerRole
                    ? 'bg-charcoal/90 backdrop-blur-xl border-t border-slate-700'
                    : 'bg-white/80 backdrop-blur-xl border-t border-sage/10 shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.05)]'
                }}
                rounded-t-2xl">
        @php
            $mobileNav = [
                ['icon' => 'dashboard', 'href' => '/dashboard', 'match' => 'dashboard', 'label' => 'Home'],
                ['icon' => 'point_of_sale', 'href' => '/pos', 'match' => 'pos', 'label' => 'POS'],
                ['icon' => 'inventory_2', 'href' => '/master/produk', 'match' => 'master/produk', 'label' => 'Stok'],
                ['icon' => 'swap_horiz', 'href' => '/retur', 'match' => 'retur', 'label' => 'Retur'],
                ['icon' => 'receipt_long', 'href' => '/transaksi/riwayat', 'match' => 'transaksi', 'label' => 'Riwayat'],
            ];
        @endphp
        @foreach($mobileNav as $mItem)
            @php $mActive = str_contains($currentRoute, $mItem['match']); @endphp
            <a href="{{ $mItem['href'] }}"
               class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl transition-all
                      {{ $mActive
                          ? ($isOwnerRole ? 'text-blue-400 bg-blue-400/10' : 'text-sage-dark bg-sage/10')
                          : ($isOwnerRole ? 'text-slate-500' : 'text-slate-400')
                      }}">
                <span class="material-symbols-outlined text-[22px]">{{ $mItem['icon'] }}</span>
                <span class="text-[9px] font-label font-bold uppercase tracking-wider">{{ $mItem['label'] }}</span>
            </a>
        @endforeach
    </nav>

    @livewireScripts
</body>
</html>