<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagLainnya extends Model
{
    use HasFactory;

    protected $table = 'tag_lainnyas';

    protected $fillable = [
        'nama',
        'no_rekening_va',
        'jumlah',
        'bank',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    public function periods()
    {
        return $this->hasMany(TagLainnyaPeriod::class)->orderByDesc('periode_tahun')->orderByDesc('periode_bulan');
    }
}
