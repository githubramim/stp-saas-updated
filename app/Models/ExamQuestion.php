<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    public const TYPE_MCQ = 'mcq';

    public const TYPE_WRITTEN = 'written';

    public const TYPE_PRACTICAL = 'practical';

    protected $fillable = [
        'exam_id', 'question_type', 'question',
        'option_a', 'option_b', 'option_c', 'option_d', 'correct_option',
        'negative_marks', 'marks', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'negative_marks' => 'decimal:2',
            'marks' => 'decimal:2',
            'display_order' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function isMcq(): bool
    {
        return $this->question_type === self::TYPE_MCQ;
    }

    public function hasNegativeMarking(): bool
    {
        return (float) $this->negative_marks > 0;
    }

    /**
     * Marks awarded for a given answer, applying negative marking on a
     * wrong MCQ answer. Anything blank scores zero.
     */
    public function awardFor(?string $givenAnswer): float
    {
        if (! $this->isMcq() || $givenAnswer === null || $givenAnswer === '') {
            return 0.0;
        }

        if (strcasecmp(trim($givenAnswer), (string) $this->correct_option) === 0) {
            return round((float) $this->marks, 2);
        }

        return -round((float) $this->negative_marks, 2);
    }

    public function isCorrect(?string $givenAnswer): ?bool
    {
        if (! $this->isMcq() || $givenAnswer === null || $givenAnswer === '') {
            return null;
        }

        return strcasecmp(trim($givenAnswer), (string) $this->correct_option) === 0;
    }

    public function options(): array
    {
        return array_filter([
            'a' => $this->option_a,
            'b' => $this->option_b,
            'c' => $this->option_c,
            'd' => $this->option_d,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
