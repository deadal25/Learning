<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevelStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level_id',
        'points',
        'is_unlocked',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_unlocked' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
