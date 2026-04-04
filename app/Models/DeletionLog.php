<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'reference_id',
        'item_code',
        'reason',
        'deleted_by_id',
        'deleted_by_name',
        'deleted_by_role',
        'snapshot',
        'deleted_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'deleted_at' => 'datetime',
    ];
}
