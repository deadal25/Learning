<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JapaneseTestQuestion extends Model
{
    use HasFactory;

    protected $table = 'japanese_test_questions';

    protected $fillable = [
        'test_id',
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

    public function test(): BelongsTo
    {
        return $this->belongsTo(JapaneseTest::class, 'test_id');
    }
}
