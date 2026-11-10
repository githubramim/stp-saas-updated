<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'institute_id', 'project_id', 'name', 'name_bn', 'course_code', 'ntvqf_level',
        'tam_sector', 'tam_occupation', 'duration', 'duration_hours', 'fee',
        'seat_capacity', 'description', 'status',
    ];

    protected function casts(): array
    {
        return [
            'duration_hours' => 'integer',
            'fee' => 'decimal:2',
            'seat_capacity' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
