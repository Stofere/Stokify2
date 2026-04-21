<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';
    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'id_produk',
        'user_id',
        'id_transaksi_penjualan',
        'id_retur',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:3',
        'stok_sebelum' => 'decimal:3',
        'stok_sesudah' => 'decimal:3',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan', 'id_transaksi_penjualan');
    }

    public function transaksiRetur(): BelongsTo
    {
        return $this->belongsTo(TransaksiRetur::class, 'id_retur', 'id_retur');
    }
}