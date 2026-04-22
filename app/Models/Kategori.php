<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'lacak_rol',
    ];

    protected $casts = [
        'lacak_rol' => 'boolean',
    ];

    public function atribut(): BelongsToMany
    {
        return $this->belongsToMany(Atribut::class, 'kategori_atribut', 'id_kategori', 'id_atribut');
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}