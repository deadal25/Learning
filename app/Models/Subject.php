<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'badge_color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name);
            }
        });
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('order', 'asc');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    public function firstLevel(): ?Level
    {
        return $this->levels()->orderBy('order', 'asc')->first();
    }
}
