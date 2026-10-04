<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress';

    protected $fillable = [
        'user_id',
        'subject_id',
        'current_level_id',
        'current_points',
        'is_completed',
    ];

    protected $casts = [
        'current_points' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function canUnlockNextLevel(): bool
    {
        return $this->current_points >= 100;
    }
}
