<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_atribut', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kategori');
            $table->unsignedBigInteger('id_atribut');

            $table->primary(['id_kategori', 'id_atribut']);

            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->onDelete('cascade');
            $table->foreign('id_atribut')->references('id_atribut')->on('atribut')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_atribut');
    }
};