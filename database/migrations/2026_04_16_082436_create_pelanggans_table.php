<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->string('nama', 255);
            $table->string('telepon', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};