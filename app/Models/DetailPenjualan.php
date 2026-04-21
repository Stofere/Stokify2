<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';

    protected $fillable = [
        'id_transaksi_penjualan',
        'id_produk',
        'jumlah',
        'jumlah_diretur',
        'satuan_saat_jual',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'decimal:3',
        'jumlah_diretur' => 'decimal:3',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Accessor cerdas untuk UI
    protected function jumlahDisplay(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $jumlah = $attributes['jumlah'];
                $satuan = strtolower($attributes['satuan_saat_jual'] ?? '');
                
                if (in_array($satuan, ['pcs', 'unit', 'buah', 'biji'])) {
                    return (int) $jumlah;
                }
                return (float) $jumlah; 
            }
        );
    }

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan', 'id_transaksi_penjualan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}