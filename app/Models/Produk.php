<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_kategori',
        'kode_barang',
        'nama_produk',
        'satuan',
        'harga_jual_satuan',
        'lacak_stok',
        'stok_saat_ini',
        'metadata',
        'index_pencarian',
        'lokasi',
        'status_aktif',
        'stok_rol',
    ];

    protected $casts = [
        'metadata' => 'array',
        'lacak_stok' => 'boolean',
        'status_aktif' => 'boolean',
        'harga_jual_satuan' => 'decimal:2',
        'stok_saat_ini' => 'decimal:3',
        'stok_rol' => 'integer',
    ];

    // Accessor: Membersihkan .00 jika satuan adalah pcs/unit
    protected function stokDisplay(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $stok = $attributes['stok_saat_ini'];
                $satuan = strtolower($attributes['satuan'] ?? '');
                
                // Jika satuannya PCS, buang desimalnya jadi integer
                if (in_array($satuan, ['pcs', 'unit', 'buah', 'biji'])) {
                    return (int) $stok;
                }
                
                // Jika meter/kg, biarkan desimal (Hapus angka 0 di belakang jika tidak perlu)
                return (float) $stok; 
            }
        );
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'id_produk', 'id_produk');
    }
}