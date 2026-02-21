<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagPlnInternetPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_pln_internet_id',
        'periode_bulan',
        'periode_tahun',
        'tagihan',
        'tanggal_payment',
    ];

    protected $casts = [
        'tagihan' => 'decimal:2',
        'tanggal_payment' => 'date',
    ];

    public function parent()
    {
        return $this->belongsTo(TagPlnInternet::class, 'tag_pln_internet_id');
    }
}
