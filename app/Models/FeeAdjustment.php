<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeAdjustment extends Model
{
    use BelongsToBranch;

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_SCHOLARSHIP = 'scholarship';

    public const TYPE_WAIVER = 'waiver';

    public const TYPES = [self::TYPE_DISCOUNT, self::TYPE_SCHOLARSHIP, self::TYPE_WAIVER];

    protected $fillable = [
        'institute_id', 'branch_id', 'student_fee_plan_id', 'type', 'amount',
        'reason', 'reference', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function studentFeePlan(): BelongsTo
    {
        return $this->belongsTo(StudentFeePlan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
