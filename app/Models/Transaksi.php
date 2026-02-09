<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = [
        'trx_id',
        'tgl_entri',
        'kode_produk',
        'nomor_tujuan',
        'status',
        'sn',
        'kode_reseller',
        'nama_reseller',
        'modul',
        'harga_beli',
        'harga_jual',
        'laba',
        'durasi_detik',
    ];

    protected $casts = [
        'tgl_entri' => 'datetime',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'laba' => 'decimal:2',
    ];

    /**
     * Scope untuk transaksi sukses
     */
    public function scopeSukses($query)
    {
        return $query->where('status', 'Sukses');
    }

    /**
     * Scope untuk transaksi gagal
     */
    public function scopeGagal($query)
    {
        return $query->where('status', 'Gagal');
    }

    /**
     * Scope filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tgl_entri', $date);
    }

    /**
     * Scope filter berdasarkan produk
     */
    public function scopeByProduk($query, $kodeProduk)
    {
        return $query->where('kode_produk', $kodeProduk);
    }

    /**
     * Scope filter berdasarkan reseller
     */
    public function scopeByReseller($query, $kodeReseller)
    {
        return $query->where('kode_reseller', $kodeReseller);
    }
}
