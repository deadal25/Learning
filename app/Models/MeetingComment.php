<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'material_id',
        'user_id',
        'subject_id',
        'class_name',
        'class_group',
        'parent_id',
        'content',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
        'level_id' => 'integer',
        'material_id' => 'integer',
        'user_id' => 'integer',
        'subject_id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MeetingComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(MeetingComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Resolves the canonical class group key (e.g. 'B', 'E', 'I', 'Semua Grup', etc.)
     */
    public static function resolveClassGroup(?string $className, ?int $subjectId = 1): string
    {
        // Khusus mata pelajaran Bahasa Jepang (subject_id = 2) atau label kelompok 'grup':
        // Seluruh grup siswa disatukan dalam satu forum terbuka per pertemuan
        if ((int)$subjectId === 2 || (!empty($className) && stripos($className, 'grup') !== false)) {
            return 'Semua Grup';
        }

        if (empty($className)) {
            return 'General';
        }

        $clean = trim($className);

        // English subject classes: B1..B8 -> B, E1 -> E, I1..I6 -> I
        if (preg_match('/(?:class|kelas|grup)?\s*([bie])\d*/i', $clean, $m)) {
            return strtoupper($m[1]);
        }

        return strtoupper($clean);
    }
}
