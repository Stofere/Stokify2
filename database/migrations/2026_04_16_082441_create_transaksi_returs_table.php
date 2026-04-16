<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_retur', function (Blueprint $table) {
            $table->id('id_retur');
            $table->string('kode_retur', 100)->unique();
            $table->unsignedBigInteger('id_transaksi_penjualan');
            $table->unsignedBigInteger('user_id'); // Referensi ke kasir/admin
            $table->dateTime('tanggal_retur');
            $table->decimal('total_biaya_retur', 15, 2)->default(0.00);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_transaksi_penjualan')->references('id_transaksi_penjualan')->on('transaksi_penjualan');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_retur');
    }
};