<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'project_id', 'course_id', 'trainer_id', 'name', 'name_bn',
        'start_date', 'end_date', 'shift', 'seat_capacity', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    protected function branchRelations(): array
    {
        return [
            ['project', 'projects.branch_id'],
            ['trainer', 'users.branch_id'],
        ];
    }
}
