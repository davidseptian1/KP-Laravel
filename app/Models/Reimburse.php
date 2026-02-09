<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimburse extends Model
{
    use HasFactory;

    protected $table = 'reimburse';

    protected $fillable = [
        'user_id',
        'form_id',
        'nama',
        'divisi',
        'kode_reimburse',
        'tanggal_pengajuan',
        'kategori',
        'nominal',
        'nama_barang',
        'keterangan',
        'keperluan',
        'bukti_file',
        'bukti_files',
        'wa_penerima',
        'wa_pengisi',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'approved_at' => 'datetime',
        'nominal' => 'decimal:2',
        'bukti_files' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function form()
    {
        return $this->belongsTo(ReimburseForm::class, 'form_id');
    }
}
