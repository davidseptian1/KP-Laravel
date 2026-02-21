<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagNomorPascaBayar extends Model
{
    use HasFactory;

    protected $table = 'tag_nomor_pasca_bayars';

    protected $fillable = [
        'nomor',
        'atas_nama',
        'chip',
        'keterangan',
        'bank',
        'status',
        'periode_des_2025_tagihan',
        'periode_des_2025_bank',
        'periode_feb_2026_tanggal_payment',
        'periode_feb_2026_tagihan',
    ];

    protected $casts = [
        'periode_des_2025_tagihan' => 'decimal:2',
        'periode_feb_2026_tagihan' => 'decimal:2',
        'periode_feb_2026_tanggal_payment' => 'date',
    ];

    public function periods()
    {
        return $this->hasMany(TagNomorPascaBayarPeriod::class)->orderByDesc('periode_tahun')->orderByDesc('periode_bulan');
    }
}
