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
}
