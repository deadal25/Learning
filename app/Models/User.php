<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STUDENT = 'student';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nrp',
        'email',
        'password',
        'role',
        'division',
        'class_name',
        'subject_id',
        'class_code',
        'phone',
        'avatar',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageLearning(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_id');
    }

    public function teacherEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'teacher_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'teacher_id');
    }

    public function getAssignedStudentIds(): array
    {
        if ($this->isSuperAdmin()) {
            return User::where('role', self::ROLE_STUDENT)->pluck('id')->toArray();
        }
        $enrolledIds = $this->teacherEnrollments()->pluck('student_id')->toArray();
        $createdIds = User::where('created_by', $this->id)->pluck('id')->toArray();
        $assigned = array_values(array_unique(array_merge($enrolledIds, $createdIds)));

        // If teacher has no specific assigned records yet, fallback to all students of their subject
        if (empty($assigned) && $this->subject_id) {
            return User::where('role', self::ROLE_STUDENT)
                ->where('subject_id', $this->subject_id)
                ->pluck('id')
                ->toArray();
        }

        return $assigned;
    }

    public function enrolledSubjects()
    {
        return $this->belongsToMany(Subject::class, 'student_enrollments', 'student_id', 'subject_id')
                    ->withPivot('class_code', 'enrolled_at', 'status')
                    ->withTimestamps();
    }

    public function isEnrolledIn($subject): bool
    {
        $subjectId = $subject instanceof Subject ? $subject->id : $subject;
        if ($this->subject_id) {
            return (int)$this->subject_id === (int)$subjectId;
        }
        return $this->enrollments()->where('subject_id', $subjectId)->exists();
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where(function ($q) use ($subjectId) {
            $q->where('users.subject_id', $subjectId)
              ->orWhere(function ($subQ) use ($subjectId) {
                  $subQ->whereNull('users.subject_id')
                       ->where(function ($cq) use ($subjectId) {
                           $cq->whereIn('users.class_name', function ($classQ) use ($subjectId) {
                               $classQ->select('name')->from('english_classes')->where('subject_id', $subjectId);
                           })
                           ->orWhere(function ($enrollQ) use ($subjectId) {
                               $enrollQ->whereNull('users.class_name')
                                       ->whereHas('enrollments', fn($eq) => $eq->where('subject_id', $subjectId));
                           });
                       });
              });

            // Ensure orphaned students without subject or enrollment are captured under Subject 1 (default)
            // so they are always visible in student management and counted consistently
            if ((int)$subjectId === 1) {
                $q->orWhere(function ($orphanQ) {
                    $orphanQ->whereNull('users.subject_id')
                            ->whereNull('users.class_name')
                            ->whereDoesntHave('enrollments');
                });
            }
        });
    }

    public function enrollInSubject(Subject $subject, ?User $teacher = null, ?string $code = null): StudentEnrollment
    {
        $enrollment = StudentEnrollment::updateOrCreate(
            [
                'student_id' => $this->id,
                'subject_id' => $subject->id,
            ],
            [
                'teacher_id' => $teacher?->id,
                'class_code' => $code ?? $teacher?->class_code,
                'enrolled_at' => now(),
                'status' => 'active',
            ]
        );

        // Initialize Level 1 and Progress if not yet exists
        $firstLevel = $subject->levels()->orderBy('order', 'asc')->first();
        if ($firstLevel) {
            UserLevelStatus::firstOrCreate([
                'user_id' => $this->id,
                'level_id' => $firstLevel->id,
            ], [
                'points' => 0,
                'is_unlocked' => true,
                'is_completed' => false,
            ]);

            UserProgress::firstOrCreate([
                'user_id' => $this->id,
                'subject_id' => $subject->id,
            ], [
                'current_level_id' => $firstLevel->id,
                'current_points' => 0,
                'is_completed' => false,
            ]);
        }

        return $enrollment;
    }

    public function createdStudents(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    public function levelStatuses(): HasMany
    {
        return $this->hasMany(UserLevelStatus::class);
    }

    public function exerciseAttempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance(): ?Attendance
    {
        return $this->attendances()->whereDate('date', now()->toDateString())->first();
    }

    public function englishGrades(): HasMany
    {
        return $this->hasMany(EnglishGrade::class, 'student_id');
    }


    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'badge-danger',
            self::ROLE_ADMIN => 'badge-primary',
            default => 'badge-success',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin (Guru)',
            default => 'Pelajar (Siswa)',
        };
    }
}
