<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPlacement extends Model
{
    public const STATUS_PLACED = 'placed';
    public const STATUS_RETAINED = 'retained';
    public const STATUS_DROPPED = 'dropped';

    protected $fillable = [
        'institute_id', 'project_id', 'student_id', 'employer_name', 'job_title',
        'employment_type', 'monthly_salary', 'placement_date', 'contact_person',
        'contact_number', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'placement_date' => 'date',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PLACED => 'Placed',
            self::STATUS_RETAINED => 'Retained',
            self::STATUS_DROPPED => 'Dropped',
        ];
    }

    public static function employmentTypes(): array
    {
        return [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'self_employed' => 'Self Employed',
            'apprenticeship' => 'Apprenticeship',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
