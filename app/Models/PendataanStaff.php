<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendataanStaff extends Model
{
    use HasFactory;

    protected $table = 'pendataan_staff';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
