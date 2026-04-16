<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atribut', function (Blueprint $table) {
            $table->id('id_atribut');
            $table->string('nama_atribut', 255);
            $table->json('pilihan_opsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atribut');
    }
};