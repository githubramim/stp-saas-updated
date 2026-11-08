<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamResult extends Model
{
    protected $fillable = [
        'institute_id', 'exam_id', 'student_id', 'batch_id',
        'total_marks', 'obtained_marks', 'percentage',
        'grade_point', 'grade_letter', 'is_pass', 'is_absent',
        'remarks', 'entered_by', 'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'obtained_marks' => 'decimal:2',
            'percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_pass' => 'boolean',
            'is_absent' => 'boolean',
            'entered_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    /**
     * Get-or-create the marksheet row for a student on an exam.
     */
    public static function forStudent(Exam $exam, Student $student): self
    {
        return static::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            [
                'institute_id' => $exam->institute_id,
                'batch_id' => $student->batch_id ?: $exam->batch_id,
                'total_marks' => $exam->total_marks,
                'is_absent' => true,
            ]
        );
    }

    /**
     * Create marksheet rows for every student in the batch who does not have one.
     */
    public static function generateSheetFor(Exam $exam): int
    {
        $created = 0;

        foreach ($exam->students()->get() as $student) {
            $before = static::where('exam_id', $exam->id)->where('student_id', $student->id)->exists();
            static::forStudent($exam, $student);
            $created += $before ? 0 : 1;
        }

        return $created;
    }

    /**
     * Recalculate the total from per-question answers (used for MCQ auto-grading).
     * Negative marking is capped so a total can never fall below zero.
     */
    public function recalculateFromAnswers(): self
    {
        $total = (float) $this->answers()->sum('marks_awarded');

        $this->obtained_marks = round(max($total, 0), 2);
        $this->is_absent = false;
        $this->applyGrade();

        return $this;
    }

    /**
     * Store a manually entered total (written/practical exams).
     */
    public function setManualMarks(float $obtained, ?string $remarks = null): self
    {
        $this->obtained_marks = round($obtained, 2);
        $this->is_absent = false;

        if ($remarks !== null) {
            $this->remarks = $remarks;
        }

        $this->applyGrade();

        return $this;
    }

    public function markAbsent(bool $absent = true): self
    {
        $this->is_absent = $absent;

        if ($absent) {
            $this->obtained_marks = 0;
            $this->percentage = 0;
            $this->grade_point = 0;
            $this->grade_letter = 'F';
            $this->is_pass = false;
        } else {
            $this->applyGrade();
        }

        return $this;
    }

    /**
     * Derive percentage, grade band and pass/fail from the current marks.
     */
    public function applyGrade(): self
    {
        $this->total_marks = (float) ($this->total_marks ?: $this->exam?->total_marks ?: 0);

        $percentage = $this->total_marks > 0
            ? round(((float) $this->obtained_marks / $this->total_marks) * 100, 2)
            : 0.0;

        $this->percentage = min($percentage, 100);

        $band = GradeScale::resolveFor($this->institute_id, (float) $this->percentage);

        $this->grade_point = $band ? (float) $band->grade_point : 0.0;
        $this->grade_letter = $band?->grade_letter ?? 'F';
        $this->is_pass = (float) $this->obtained_marks >= (float) ($this->exam?->pass_marks ?? 0);

        return $this;
    }

    /**
     * Rank within the exam; students on equal marks share a position.
     */
    public function meritPosition(): int
    {
        if ($this->is_absent) {
            return 0;
        }

        $higher = static::where('exam_id', $this->exam_id)
            ->where('is_absent', false)
            ->where('obtained_marks', '>', (float) $this->obtained_marks)
            ->count();

        return $higher + 1;
    }

    /**
     * Credit-weighted CGPA across every published exam the student sat.
     */
    public static function cgpaFor(int $studentId, ?int $instituteId = null): float
    {
        $results = static::with('exam')
            ->where('student_id', $studentId)
            ->where('is_absent', false)
            ->when($instituteId, fn ($q) => $q->where('institute_id', $instituteId))
            ->whereHas('exam', fn ($q) => $q->where('status', Exam::STATUS_PUBLISHED))
            ->where('total_marks', '>', 0)
            ->get();

        $credits = (float) $results->sum('total_marks');
        $points = (float) $results->sum(fn ($r) => (float) $r->grade_point * (float) $r->total_marks);

        return $credits > 0 ? round($points / $credits, 2) : 0.0;
    }
}
