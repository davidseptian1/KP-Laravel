<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagNomorPascaBayarPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_nomor_pasca_bayar_id',
        'periode_bulan',
        'periode_tahun',
        'tagihan',
        'bank',
        'tanggal_payment',
    ];

    protected $casts = [
        'tagihan' => 'decimal:2',
        'tanggal_payment' => 'date',
    ];

    public function parent()
    {
        return $this->belongsTo(TagNomorPascaBayar::class, 'tag_nomor_pasca_bayar_id');
    }
}
