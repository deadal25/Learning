<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'teacher_id',
        'class_name',
        'title',
        'description',
        'content',
        'file_path',
        'file_name',
        'file_type',
        'slide_url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    public function isPdf(): bool
    {
        $ext = strtolower($this->file_type ?? '');
        $path = strtolower($this->file_path ?? '');
        $name = strtolower($this->file_name ?? '');

        return $ext === 'pdf' ||
               str_contains($ext, 'pdf') ||
               str_ends_with($name, '.pdf') ||
               str_ends_with($path, '.pdf');
    }

    public function isPpt(): bool
    {
        if ($this->isPdf()) {
            return false;
        }

        $ext = strtolower($this->file_type ?? '');
        $path = strtolower($this->file_path ?? '');
        $name = strtolower($this->file_name ?? '');

        return in_array($ext, ['ppt', 'pptx', 'pps', 'ppsx']) ||
               str_contains($ext, 'presentation') ||
               str_contains($ext, 'powerpoint') ||
               preg_match('/\.(pptx?|ppsx?)$/i', $name) ||
               preg_match('/\.(pptx?|ppsx?)$/i', $path);
    }

    public function isImage(): bool
    {
        $ext = strtolower($this->file_type ?? '');
        return in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg']) ||
               preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', $this->file_name ?? '');
    }

    public function isDoc(): bool
    {
        $ext = strtolower($this->file_type ?? '');
        return in_array($ext, ['doc', 'docx', 'txt', 'rtf', 'odt']) ||
               preg_match('/\.(docx?|txt|rtf|odt)$/i', $this->file_name ?? '');
    }

    public function getFileIconAttribute(): string
    {
        if ($this->isPdf()) return '📕';
        if ($this->isPpt()) return '📊';
        if ($this->isImage()) return '🖼️';
        if ($this->isDoc()) return '📄';
        if ($this->slide_url) return '🌐';
        return '📚';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    public function isCanva(): bool
    {
        if (!$this->slide_url) return false;
        return str_contains($this->slide_url, 'canva.com') || str_contains($this->slide_url, 'canva.link');
    }

    public function getEmbedSlideUrlAttribute(): ?string
    {
        if (!$this->slide_url) return null;

        $url = trim($this->slide_url);

        // Convert Canva design link to embed link if applicable
        if (str_contains($url, 'canva.com/design/') && !str_contains($url, 'embed')) {
            // Replace /edit or /view with /view?embed
            $url = preg_replace('/(\/edit|\/view)(\?.*)?$/', '/view?embed', $url);
            if (!str_contains($url, 'embed')) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'embed';
            }
        }

        // Convert Google Slides to embed format if needed
        if (str_contains($url, 'docs.google.com/presentation') && !str_contains($url, '/embed')) {
            $url = preg_replace('/(\/edit|\/pub)(\?.*)?$/', '/embed?start=false&loop=false&delayms=3000', $url);
        }

        return $url;
    }

    public function getFileTypeLabelAttribute(): string
    {
        if ($this->isPdf()) return 'Dokumen PDF';
        if ($this->isPpt()) return 'Slide Presentasi PPT';
        if ($this->isImage()) return 'Gambar / Bagan Belajar';
        if ($this->isDoc()) return 'Dokumen Word / Teks';
        if ($this->slide_url) return 'Slide Interaktif Web';
        return 'Modul Belajar';
    }

    /**
     * Format display label for class_name (e.g. 'I' -> 'Seluruh Kelas I (I1 - I6)').
     */
    public function getFormattedClassLabelAttribute(): string
    {
        if (empty($this->class_name)) {
            return 'Semua Kelas';
        }

        $c = trim($this->class_name);
        if (strtoupper($c) === 'I') {
            return 'Seluruh Kelas I (I1 - I6)';
        }
        if (strtoupper($c) === 'B') {
            return 'Seluruh Kelas B (B1 - B8)';
        }
        if (strtoupper($c) === 'E') {
            return 'Seluruh Kelas E (E1)';
        }

        return $c;
    }

    /**
     * Check if this material is accessible to a student registered in $studentClassName.
     */
    public function isAccessibleByClass(?string $studentClassName): bool
    {
        if (empty($this->class_name)) {
            return true;
        }

        if (empty($studentClassName)) {
            return false;
        }

        $targetClass = trim($this->class_name);
        $studentClass = trim($studentClassName);

        // Exact match
        if (strcasecmp($targetClass, $studentClass) === 0) {
            return true;
        }

        // Letter group matching (e.g. student in 'I1', 'I2' matches target 'I', 'Grup I', 'Kelas I')
        $studentPrefix = strtoupper(substr($studentClass, 0, 1));
        $allowedPrefixes = [
            $studentPrefix,
            'Grup ' . $studentPrefix,
            'Kelas ' . $studentPrefix,
            'Class ' . $studentPrefix,
        ];

        foreach ($allowedPrefixes as $ap) {
            if (strcasecmp($targetClass, $ap) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope query to only materials accessible by student's class (including letter group match and general materials).
     */
    public function scopeForStudentClass($query, ?string $studentClassName)
    {
        if (empty($studentClassName)) {
            return $query;
        }

        $trimmed = trim($studentClassName);
        $prefix = strtoupper(substr($trimmed, 0, 1));

        $allowed = array_unique([
            $trimmed,
            $prefix,
            'Grup ' . $prefix,
            'Kelas ' . $prefix,
            'Class ' . $prefix,
        ]);

        return $query->where(function ($q) use ($allowed) {
            $q->whereIn('class_name', $allowed)
              ->orWhereNull('class_name')
              ->orWhere('class_name', '');
        });
    }
}
