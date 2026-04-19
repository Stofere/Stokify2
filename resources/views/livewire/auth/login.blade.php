{{-- Wrapper Utama dengan Latar Belakang Silver-White yang Bersih --}}
<div class="min-h-screen bg-[#F8F9FA] flex flex-col items-center justify-center p-6 antialiased">

    {{-- Container Kartu Login - Dibuat lebih ramping dan rigid untuk kesan profesional --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 border border-gray-100 relative overflow-hidden">
        
        {{-- Aksen Dekoratif Halus (Garis Emas Tipis di bagian paling atas) --}}
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#84A59D] via-[#D4AF37] to-[#84A59D]"></div>

        {{-- Header Tampilan --}}
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#F8F9FA] rounded-full mb-6 border border-gray-50">
                {{-- Icon Logistik/Box Minimalis --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#84A59D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-[#334155] tracking-tight font-serif uppercase">
                STOKIFY<span class="text-[#84A59D] font-light">v2</span>
            </h1>
            <p class="text-gray-400 mt-2 text-xs uppercase tracking-[0.2em] font-medium">Inventory & Point of Sales</p>
        </div>

        {{-- Area Error (Livewire) --}}
        @error('login') 
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-center border border-red-100 text-xs font-semibold">
                {{ $message }}
            </div> 
        @enderror

        {{-- Form Login (Livewire) --}}
        <form wire:submit="prosesLogin" class="space-y-5">
            
            {{-- Input Username --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Kredensial Pengguna</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-300 group-focus-within:text-[#84A59D] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input type="text" 
                           wire:model="username" 
                           placeholder="Username"
                           class="w-full pl-12 pr-4 py-3.5 border border-gray-100 rounded-xl focus:ring-4 focus:ring-[#84A59D]/5 focus:border-[#84A59D] bg-[#FBFBFB] transition-all duration-200 text-sm text-gray-700 placeholder:text-gray-300">
                </div>
                @error('username') <span class="text-red-500 text-[10px] mt-1 ml-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Input Password --}}
            <div>
                <div class="flex justify-between items-center mb-2 ml-1">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest">Kata Sandi</label>
                </div>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-300 group-focus-within:text-[#84A59D] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input type="password" 
                           wire:model="password" 
                           placeholder="••••••••"
                           class="w-full pl-12 pr-4 py-3.5 border border-gray-100 rounded-xl focus:ring-4 focus:ring-[#84A59D]/5 focus:border-[#84A59D] bg-[#FBFBFB] transition-all duration-200 text-sm text-gray-700 placeholder:text-gray-300">
                </div>
                @error('password') <span class="text-red-500 text-[10px] mt-1 ml-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Tombol Submit --}}
            <div class="pt-4">
                <button type="submit" 
                        class="w-full bg-[#334155] hover:bg-[#1e293b] text-white font-bold py-4 rounded-xl shadow-lg shadow-gray-200 transition-all duration-300 tracking-widest text-xs flex items-center justify-center gap-3 group">
                    OTENTIKASI SISTEM
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        {{-- Footer Branding - Menegaskan Identitas Developer --}}
        <div class="mt-12 pt-8 border-t border-gray-50 flex flex-col items-center">
            <p class="text-[10px] text-gray-300 uppercase tracking-[0.3em] mb-2">Developed & Engineered by</p>
            <p class="text-sm font-serif italic text-[#84A59D] font-semibold">Roger Jeremy</p>
        </div>
    </div>

    {{-- Info Versi Luar Card (Opsional) --}}
    <div class="mt-8 text-center">
        <p class="text-[10px] text-gray-400 font-medium tracking-widest">SYSTEM BUILD v2.0.0-RELEASE</p>
    </div>
</div>