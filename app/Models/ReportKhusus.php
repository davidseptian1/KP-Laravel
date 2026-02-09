<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportKhusus extends Model
{
    use HasFactory;

    protected $table = 'report_khusus';

    protected $fillable = [
        'tanggal',
        'nama',
        'produk',
        'nomor_tujuan',
        'supplier',
        'total',
        'server',
        'note',
    ];
}
