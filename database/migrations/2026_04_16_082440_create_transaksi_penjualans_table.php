<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id('id_transaksi_penjualan');
            $table->string('kode_nota', 100)->unique();
            $table->unsignedBigInteger('user_id'); // Referensi ke tabel users
            $table->unsignedBigInteger('id_pelanggan')->nullable();
            $table->unsignedBigInteger('id_marketing')->nullable();
            $table->decimal('total_harga', 15, 2);
            $table->enum('status_penjualan', ['PESANAN', 'DIBATALKAN', 'DIRETUR', 'SELESAI'])->default('SELESAI');
            $table->dateTime('tanggal_transaksi');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
            $table->foreign('id_marketing')->references('id_marketing')->on('marketing');
            
            $table->index('tanggal_transaksi', 'idx_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan');
    }
};