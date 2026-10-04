<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'order',
        'description',
        'required_points',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'required_points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->orderBy('order', 'asc');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('question_number', 'asc');
    }

    public function userStatuses(): HasMany
    {
        return $this->hasMany(UserLevelStatus::class);
    }

    public function nextLevel(): ?Level
    {
        return self::where('subject_id', $this->subject_id)
            ->where('order', '>', $this->order)
            ->orderBy('order', 'asc')
            ->first();
    }

    public function previousLevel(): ?Level
    {
        return self::where('subject_id', $this->subject_id)
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }
}
