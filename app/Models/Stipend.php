<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stipend extends Model
{
    use BelongsToBranch;

    public const TYPE_STIPEND = 'stipend';

    public const TYPE_ALLOWANCE = 'allowance';

    public const TYPE_TRANSPORT = 'transport';

    public const TYPE_FOOD = 'food';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'institute_id', 'project_id', 'student_id', 'type', 'period', 'amount',
        'payment_date', 'payment_method', 'bank_name', 'account_no',
        'transaction_ref', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_STIPEND => 'Stipend',
            self::TYPE_ALLOWANCE => 'Allowance',
            self::TYPE_TRANSPORT => 'Transport',
            self::TYPE_FOOD => 'Food',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
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

    public function periodLabel(): string
    {
        if (! $this->period || ! preg_match('/^\d{4}-\d{2}$/', $this->period)) {
            return $this->period ?: 'â€”';
        }

        return Carbon::createFromFormat('Y-m', $this->period)->format('M Y');
    }

    protected function branchRelations(): array
    {
        return [
            ['project', 'projects.branch_id'],
            ['student', 'students.branch_id'],
        ];
    }
}
