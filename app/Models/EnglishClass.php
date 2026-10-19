<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnglishClass extends Model
{
    use HasFactory;

    protected $table = 'english_classes';

    protected $fillable = [
        'subject_id',
        'name',
        'level_name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get students enrolled in this specific class name.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'class_name', 'name')
            ->where('role', User::ROLE_STUDENT);
    }

    /**
     * Get learning materials assigned to this class.
     */
    public function materials()
    {
        return $this->hasMany(Material::class, 'class_name', 'name');
    }

    /**
     * Scope to order by natural sort order.
     */
    public function scopeAlphabetical($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Reorder all classes in the database alphabetically (natural sort)
     * so that their sort_order numbers permanently match their alphabetical order.
     */
    public static function reorderAlphabetically(?int $subjectId = null): void
    {
        $query = static::query();
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        $classes = $query->get();

        $bySubject = $classes->groupBy('subject_id');
        foreach ($bySubject as $subjId => $group) {
            $sorted = $group->sortBy(function ($item) {
                return $item->name;
            }, SORT_NATURAL | SORT_FLAG_CASE)->values();

            foreach ($sorted as $index => $class) {
                $newSort = $index + 1;
                if ($class->sort_order !== $newSort) {
                    \Illuminate\Support\Facades\DB::table('english_classes')
                        ->where('id', $class->id)
                        ->update(['sort_order' => $newSort]);
                }
            }
        }
    }
}
