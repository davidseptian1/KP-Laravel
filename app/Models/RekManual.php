<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekManual extends Model
{
    use HasFactory;

    protected $table = 'rek_manuals';

    protected $fillable = [
        'bank_tujuan',
        'no_rek',
        'nama_rekening',
        'keterangan',
    ];
}
