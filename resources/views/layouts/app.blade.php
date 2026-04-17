<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stokify POS V2 - Enterprise</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-center">
            <h1 class="text-2xl font-black text-blue-400 tracking-wider">STOKIFY<span class="text-white">v2</span></h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg font-semibold transition">
                📊 Dashboard
            </a>
            <div class="pt-4 pb-1 text-xs text-gray-500 uppercase font-bold tracking-wider">Aktivitas Toko</div>
            <a href="/pos" class="flex items-center gap-3 px-4 py-3 bg-gray-800 rounded-lg text-blue-300 font-bold hover:bg-gray-700 transition">
                🛒 Kasir POS
            </a>
            <a href="/retur" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg font-semibold transition">
                🔄 Retur Barang
            </a>
            <a href="/stok/penyesuaian" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg text-red-300 font-semibold transition">
                ⚠️ Adjust Stok
            </a>

            <div class="pt-4 pb-1 text-xs text-gray-500 uppercase font-bold tracking-wider">Master Data</div>
            <a href="/master/produk" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg font-semibold transition">
                📦 Katalog Produk
            </a>
            <a href="/master/pelanggan" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg font-semibold transition">
                👥 Data Pelanggan
            </a>
            
            <form action="/logout" method="POST" class="mt-8 border-t border-gray-800 pt-4">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white font-bold transition">
                    Logout
                </button>
            </form>
        </nav>

        <div class="p-4 border-t border-gray-800 text-xs text-gray-500 text-center">
            Login sebagai: <br><strong class="text-white">Bapak Owner (OWNER)</strong>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center z-10">
            <h2 class="text-xl font-bold text-gray-800">Sistem Internal Enterprise</h2>
            <div class="text-sm text-gray-600 font-semibold border px-3 py-1 rounded bg-gray-50">Terkoneksi Database</div>
        </header>
        
        <!-- Livewire Component di-render di sini -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 relative">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>