<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'section_id',
        'created_by',
        'title',
        'description',
        'deadline',
        'allow_file_upload',
        'submission_type',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'allow_file_upload' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The section this assignment belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Section::class);
    }

    /**
     * Creator (doctor) of the assignment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Submissions for this assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(\App\Models\Submission::class);
    }

    public function latestSubmission()
{
    return $this->hasOne(\App\Models\Submission::class)->latestOfMany('submitted_at');
}
}
