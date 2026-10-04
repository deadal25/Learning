<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnglishGrade extends Model
{
    use HasFactory;

    protected $table = 'english_grades';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'class_name',
        'week',
        'meeting_1',
        'meeting_2',
        'meeting_3',
        'meeting_4',
        'attendance_score',
        'fluency',
        'grammar',
        'pronunciation',
        'vocabulary',
        'total_exam',
        'final_score',
        'feedback',
    ];

    protected $casts = [
        'week' => 'integer',
        'meeting_1' => 'float',
        'meeting_2' => 'float',
        'meeting_3' => 'float',
        'meeting_4' => 'float',
        'attendance_score' => 'float',
        'fluency' => 'float',
        'grammar' => 'float',
        'pronunciation' => 'float',
        'vocabulary' => 'float',
        'total_exam' => 'float',
        'final_score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Calculate automatic attendance score from meetings if needed.
     */
    public function calculateAttendance(): float
    {
        return round(($this->meeting_1 + $this->meeting_2 + $this->meeting_3 + $this->meeting_4) / 4, 2);
    }

    /**
     * Calculate total exam score (Fluency + Grammar + Pronunciation + Vocabulary) / 4.
     */
    public function calculateTotalExam(): float
    {
        return round(($this->fluency + $this->grammar + $this->pronunciation + $this->vocabulary) / 4, 2);
    }

    /**
     * Calculate final score (Attendance + Total Exam) / 2.
     */
    public function calculateFinalScore(): float
    {
        return round(($this->attendance_score + $this->total_exam) / 2, 2);
    }
}
