<div class="w-full max-w-md bg-white rounded-lg shadow-xl p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-blue-600 tracking-wider">STOKIFY<span class="text-gray-800">v2</span></h1>
        <p class="text-gray-500 mt-2 text-sm">Enterprise ERP & POS System</p>
    </div>

    @error('login') 
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center font-semibold text-sm">{{ $message }}</div> 
    @enderror

    <form wire:submit="prosesLogin" class="space-y-5">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Username</label>
            <input type="text" wire:model="username" class="w-full px-4 py-3 border rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
            @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Password</label>
            <input type="password" wire:model="password" class="w-full px-4 py-3 border rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition">
            MASUK SISTEM
        </button>
    </form>
</div>