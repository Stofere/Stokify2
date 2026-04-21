<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            // Menyimpan jumlah fisik (KG) yang benar-benar dipotong dari gudang saat jual per Meter.
            // Untuk barang biasa (non-dual-unit), nilainya sama dengan `jumlah`.
            // Nullable karena data transaksi lama belum memiliki kolom ini (fallback ke `jumlah`).
            $table->decimal('jumlah_potong_gudang', 15, 3)->nullable()->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn('jumlah_potong_gudang');
        });
    }
};
