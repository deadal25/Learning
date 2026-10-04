<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JapaneseTestSubmission extends Model
{
    use HasFactory;

    protected $table = 'japanese_test_submissions';

    protected $fillable = [
        'test_id',
        'student_id',
        'score',
        'total_questions',
        'correct_count',
        'is_passed',
        'submitted_answers',
        'teacher_feedback',
    ];

    protected $casts = [
        'score' => 'integer',
        'total_questions' => 'integer',
        'correct_count' => 'integer',
        'is_passed' => 'boolean',
        'submitted_answers' => 'array',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(JapaneseTest::class, 'test_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
