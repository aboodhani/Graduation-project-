<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
protected $fillable = [
    'assignment_id',
    'student_id',
    'student_name',      // optional: if you have this column
    'student_number',    // optional: if you have this column
    'content',
    'score',
    'file_path',
    'image_path',
    'label',
    'code',
    'submitted_at',
    'grade',
    'feedback',
    'graded_by',
    'graded_at',
    'section_id',        // optional: if you have it
];


    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Assignment relation.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Assignment::class);
    }

    /**
     * Student (user) relation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    /**
     * Grader (doctor) relation.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'graded_by');
    }

    /**
     * Convenience accessor: image URL (storage or absolute URL).
     */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->attributes['image_path'] ?? $this->attributes['file_path'] ?? null;
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Scope: submissions for a given student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
    public function section()
{
    return $this->belongsTo(\App\Models\Section::class, 'section_id');
}
}
