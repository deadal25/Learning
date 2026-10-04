<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JapaneseTest extends Model
{
    use HasFactory;

    protected $table = 'japanese_tests';

    protected $fillable = [
        'title',
        'category',
        'target_questions',
        'start_meeting',
        'end_meeting',
        'description',
        'duration_minutes',
        'pass_score',
        'is_active',
    ];

    protected $casts = [
        'target_questions' => 'integer',
        'start_meeting' => 'integer',
        'end_meeting' => 'integer',
        'duration_minutes' => 'integer',
        'pass_score' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isMeetingTest(): bool
    {
        return $this->category === 'per_pertemuan';
    }

    public function isPeriodicTest(): bool
    {
        return $this->category === 'per_4_pertemuan';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(JapaneseTestQuestion::class, 'test_id')->orderBy('question_number', 'asc');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(JapaneseTestSubmission::class, 'test_id');
    }

    public function latestSubmissionFor(int $userId)
    {
        return $this->submissions()->where('student_id', $userId)->latest()->first();
    }
}
