<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeePlan extends Model
{
    use BelongsToBranch;

    public const MONTHS = [3, 6, 12];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'fee_plan_id', 'course_id', 'batch_id',
        'code', 'plan_months', 'gross_amount', 'discount_amount', 'scholarship_amount',
        'waiver_amount', 'net_amount', 'start_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'plan_months' => 'integer',
            'gross_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'scholarship_amount' => 'decimal:2',
            'waiver_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'start_date' => 'date',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feePlan(): BelongsTo
    {
        return $this->belongsTo(FeePlan::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(FeeAdjustment::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class)->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->latest('payment_date');
    }

    public function getAdjustmentsTotalAttribute(): float
    {
        return (float) $this->discount_amount + (float) $this->scholarship_amount + (float) $this->waiver_amount;
    }

    public function getPaidAttribute(): float
    {
        return (float) $this->installments()->sum('paid_amount');
    }

    public function getDueAttribute(): float
    {
        return max(0, (float) $this->net_amount - $this->paid);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Keep the snapshot columns in sync with the adjustment ledger. */
    public function recalculateAmounts(): void
    {
        $totals = $this->adjustments()
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $this->discount_amount = (float) ($totals->get(FeeAdjustment::TYPE_DISCOUNT, 0));
        $this->scholarship_amount = (float) ($totals->get(FeeAdjustment::TYPE_SCHOLARSHIP, 0));
        $this->waiver_amount = (float) ($totals->get(FeeAdjustment::TYPE_WAIVER, 0));
        $this->net_amount = max(0, (float) $this->gross_amount - $this->adjustments_total);
        $this->save();
    }
}
