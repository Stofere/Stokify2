<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id('id_detail_penjualan');
            $table->unsignedBigInteger('id_transaksi_penjualan');
            $table->unsignedBigInteger('id_produk');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('jumlah_diretur', 15, 2)->default(0.00);
            $table->string('satuan_saat_jual', 50);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->foreign('id_transaksi_penjualan')->references('id_transaksi_penjualan')->on('transaksi_penjualan')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};