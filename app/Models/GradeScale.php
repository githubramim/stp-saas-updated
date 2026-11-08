<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    protected $fillable = [
        'institute_id', 'min_percentage', 'max_percentage',
        'grade_point', 'grade_letter', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'min_percentage' => 'decimal:2',
            'max_percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
        ];
    }

    /**
     * Find the band a percentage falls into, preferring an institute override
     * over the shared/global scale.
     */
    public static function resolveFor(?int $instituteId, float $percentage): ?self
    {
        $query = static::query()
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage);

        if ($instituteId) {
            $query->where(function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId)->orWhereNull('institute_id');
            })
                ->orderByRaw('institute_id is null');
        } else {
            $query->whereNull('institute_id');
        }

        return $query->first();
    }
}
