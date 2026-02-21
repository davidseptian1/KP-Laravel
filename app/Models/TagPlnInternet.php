<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagPlnInternet extends Model
{
    use HasFactory;

    protected $table = 'tag_pln_internets';

    protected $fillable = [
        'nama',
        'nomor_pln_internet',
        'atas_nama',
        'bank',
        'keterangan',
        'periode_januari_2026_tagihan',
        'periode_januari_2026_tanggal_payment',
        'periode_februari_2026_tagihan',
        'periode_februari_2026_tanggal_payment',
    ];

    protected $casts = [
        'periode_januari_2026_tagihan' => 'decimal:2',
        'periode_februari_2026_tagihan' => 'decimal:2',
        'periode_januari_2026_tanggal_payment' => 'date',
        'periode_februari_2026_tanggal_payment' => 'date',
    ];

    public function periods()
    {
        return $this->hasMany(TagPlnInternetPeriod::class)->orderByDesc('periode_tahun')->orderByDesc('periode_bulan');
    }
}
