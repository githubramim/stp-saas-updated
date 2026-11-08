<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const TYPE_MCQ = 'mcq';

    public const TYPE_WRITTEN = 'written';

    public const TYPE_PRACTICAL = 'practical';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'institute_id', 'project_id', 'course_id', 'batch_id',
        'title', 'title_bn', 'exam_type', 'total_marks', 'pass_marks',
        'exam_date', 'duration_minutes', 'instructions', 'status',
        'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'total_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'published_at' => 'datetime',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('display_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function students()
    {
        return Student::where('batch_id', $this->batch_id)->orderBy('name_en');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasMcq(): bool
    {
        return $this->questions->contains('question_type', ExamQuestion::TYPE_MCQ);
    }

    /**
     * Sum of question marks — the authoritative total for the marksheet.
     */
    public function calculatedTotalMarks(): float
    {
        return round((float) $this->questions->sum('marks'), 2);
    }

    public function mcqTotalMarks(): float
    {
        return round((float) $this->questions->where('question_type', ExamQuestion::TYPE_MCQ)->sum('marks'), 2);
    }
}
