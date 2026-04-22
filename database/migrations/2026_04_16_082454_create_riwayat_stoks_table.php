<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->unsignedBigInteger('id_produk');
            $table->unsignedBigInteger('user_id'); // Pelaku perubah stok
            $table->unsignedBigInteger('id_transaksi_penjualan')->nullable();
            $table->unsignedBigInteger('id_retur')->nullable();
            $table->enum('tipe', ['AWAL', 'MASUK', 'KELUAR', 'KOREKSI_PLUS', 'KOREKSI_MINUS', 'BATAL_JUAL', 'ROL_MASUK', 'ROL_KELUAR']);
            $table->decimal('jumlah', 15, 3);
            $table->decimal('stok_sebelum', 15, 3);
            $table->decimal('stok_sesudah', 15, 3);
            $table->string('keterangan', 255);
            $table->integer('rol_mutasi')->nullable();   // Jumlah Rol yang berubah (hanya terisi saat mutasi Rol)
            $table->integer('rol_sebelum')->nullable();  // Sisa Rol sebelum mutasi
            $table->integer('rol_sesudah')->nullable();  // Sisa Rol sesudah mutasi
            $table->timestamps();

            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_transaksi_penjualan')->references('id_transaksi_penjualan')->on('transaksi_penjualan')->onDelete('set null');
            $table->foreign('id_retur')->references('id_retur')->on('transaksi_retur')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};