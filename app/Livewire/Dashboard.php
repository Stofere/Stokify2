<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TransaksiPenjualan;
use App\Models\TransaksiRetur;

class Dashboard extends Component
{
    public function render()
    {
        $hariIni = today();

        // Omset Kotor Penjualan Hari Ini
        $omsetHariIni = TransaksiPenjualan::whereDate('tanggal_transaksi', $hariIni)
                            ->where('status_penjualan', '!=', 'DIBATALKAN')
                            ->sum('total_harga');

        // Total Kerugian/Selisih Retur Hari Ini (Jika toko mengembalikan uang)
        $returHariIni = TransaksiRetur::whereDate('tanggal_retur', $hariIni)->sum('total_biaya_retur');

        $notaCount = TransaksiPenjualan::whereDate('tanggal_transaksi', $hariIni)->count();

        return view('livewire.dashboard', [
            'omsetHariIni' => $omsetHariIni,
            'returHariIni' => $returHariIni,
            'notaCount' => $notaCount
        ]);
    }
}