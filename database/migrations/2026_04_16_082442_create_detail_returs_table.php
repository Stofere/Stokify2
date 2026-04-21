<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_retur', function (Blueprint $table) {
            $table->id('id_detail_retur');
            $table->unsignedBigInteger('id_retur');
            $table->unsignedBigInteger('id_produk_dikembalikan');
            $table->unsignedBigInteger('id_produk_pengganti');
            $table->decimal('jumlah', 15, 3);
            $table->enum('kondisi_barang_dikembalikan', ['BAGUS', 'RUSAK']);
            $table->decimal('subtotal_biaya', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('id_retur')->references('id_retur')->on('transaksi_retur')->onDelete('cascade');
            $table->foreign('id_produk_dikembalikan')->references('id_produk')->on('produk');
            $table->foreign('id_produk_pengganti')->references('id_produk')->on('produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_retur');
    }
};