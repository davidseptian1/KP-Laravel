<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagLainnyaPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_lainnya_id',
        'periode_bulan',
        'periode_tahun',
        'tagihan',
        'tanggal_payment',
    ];

    protected $casts = [
        'tagihan' => 'decimal:2',
        'tanggal_payment' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(TagLainnya::class, 'tag_lainnya_id');
    }
}
