<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'action_type',
        'method',
        'route_name',
        'path',
        'target_model',
        'target_id',
        'ip_address',
        'user_agent',
        'status_code',
        'change_summary',
        'request_data',
        'before_data',
        'after_data',
    ];

    protected $casts = [
        'request_data' => 'array',
        'before_data' => 'array',
        'after_data' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
