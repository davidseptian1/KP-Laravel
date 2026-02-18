<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Deposit extends Model
{
    use HasFactory;

    protected $table = 'deposits';

    protected $fillable = [
        'user_id',
        'form_id',
        'nama_supplier',
        'jenis_transaksi',
        'nominal',
        'bank',
        'server',
        'no_rek',
        'nama_rekening',
        'reply_tiket',
        'reply_penambahan',
        'status',
        'jam',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'jam' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
