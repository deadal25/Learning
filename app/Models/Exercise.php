<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'question_number',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'points',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'points' => 'integer',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function getOptionsAttribute(): array
    {
        return [
            'a' => $this->option_a,
            'b' => $this->option_b,
            'c' => $this->option_c,
            'd' => $this->option_d,
        ];
    }
}
