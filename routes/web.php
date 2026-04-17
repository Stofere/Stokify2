<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Transaksi\KasirPos;
use App\Livewire\Transaksi\ReturPenjualan;
use App\Livewire\Master\PenyesuaianStok;
use App\Livewire\Master\PelangganIndex;
use App\Livewire\Master\ProdukIndex;

// Route Authentication (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', function () { return redirect('/login'); });
});

// Route System (Harus Login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Transaksi
    Route::get('/pos', KasirPos::class)->name('pos');
    Route::get('/retur', ReturPenjualan::class)->name('retur');
    Route::get('/stok/penyesuaian', PenyesuaianStok::class)->name('stok.penyesuaian');

    // Master Data
    Route::get('/master/pelanggan', PelangganIndex::class)->name('master.pelanggan');
    Route::get('/master/produk', ProdukIndex::class)->name('master.produk');
    
    // Proses Logout (Non-Livewire Standard Route)
    Route::post('/logout', function() {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    });
});