<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'name',  // <-- THIS IS THE FIX
        'course_name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Creator (doctor) of this section.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Students registered in this section (many-to-many pivot: section_user).
     */
public function users(): BelongsToMany
{
    return $this->belongsToMany(\App\Models\User::class, 'section_user')
            ->withTimestamps();
}
    /**
     * Assignments that belong to this section.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(\App\Models\Assignment::class);
    }

    /**
     * Helper: return count of registered students.
     */
    public function getStudentsCountAttribute(): int
    {
        // if relation loaded, use it; otherwise query
        if ($this->relationLoaded('users')) {
            return $this->users->count();
        }
        return $this->users()->count();
    }
    public function students()
{
    return $this->belongsToMany(\App\Models\User::class, 'section_user', 'section_id', 'user_id')
                ->withTimestamps(); // إن كان عندك أعمدة timestamps في pivot
}
}
