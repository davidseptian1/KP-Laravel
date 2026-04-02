<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekManual extends Model
{
    use HasFactory;

    protected $table = 'rek_manuals';

    protected $fillable = [
        'supplier_id',
        'bank_tujuan',
        'no_rek',
        'nama_rekening',
        'keterangan',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
