<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInstallment extends Model
{
    use BelongsToBranch;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_WAIVED = 'waived';

    public const STATUSES = [
        self::STATUS_UNPAID, self::STATUS_PARTIAL, self::STATUS_PAID,
        self::STATUS_OVERDUE, self::STATUS_WAIVED,
    ];

    protected $fillable = [
        'institute_id', 'branch_id', 'student_fee_plan_id', 'student_id',
        'sequence', 'due_date', 'amount', 'paid_amount', 'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    /** Short alias used by the fee installment tables. */
    public function plan(): BelongsTo
    {
        return $this->studentFeePlan();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->latest('payment_date');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(FeeDueReminder::class)->latest('due_date');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getIsDueAttribute(): bool
    {
        return $this->balance > 0
            && $this->status !== self::STATUS_WAIVED
            && $this->due_date?->isPast();
    }

    /** Recompute status from amounts and the due date. */
    public function syncStatus(): void
    {
        if ($this->status === self::STATUS_WAIVED) {
            return;
        }

        $paid = (float) $this->paid_amount;
        $amount = (float) $this->amount;

        if ($paid >= $amount && $amount > 0) {
            $this->status = self::STATUS_PAID;
            $this->paid_at ??= now();
        } elseif ($paid > 0) {
            $this->status = self::STATUS_PARTIAL;
        } else {
            $this->status = $this->due_date?->isPast()
                ? self::STATUS_OVERDUE
                : self::STATUS_UNPAID;
        }

        $this->save();
    }
}
