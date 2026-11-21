<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    use BelongsToBranch;
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const EMPLOYMENT_TYPES = ['full_time', 'part_time', 'contract', 'intern'];

    protected $fillable = [
        'institute_id', 'user_id', 'employee_code', 'joining_date', 'employment_type',
        'department', 'basic_salary', 'house_allowance', 'transport_allowance',
        'other_allowance', 'annual_leave_entitlement', 'emergency_contact', 'nid',
        'performance_rating', 'performance_notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'basic_salary' => 'decimal:2',
            'house_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'other_allowance' => 'decimal:2',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gross monthly salary from the structured components.
     */
    public function grossSalary(): float
    {
        return round(
            (float) $this->basic_salary
            + (float) $this->house_allowance
            + (float) $this->transport_allowance
            + (float) $this->other_allowance,
            2
        );
    }

    public function totalAllowance(): float
    {
        return round(
            (float) $this->house_allowance + (float) $this->transport_allowance + (float) $this->other_allowance,
            2
        );
    }

    /**
     * Approved leave days taken in a calendar year.
     */
    public function leaveTaken(int $year): float
    {
        return (float) EmployeeLeave::where('user_id', $this->user_id)
            ->where('status', EmployeeLeave::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->sum('days');
    }

    /**
     * Remaining annual leave balance (never negative).
     */
    public function leaveBalance(int $year): float
    {
        return max(0, (float) $this->annual_leave_entitlement - $this->leaveTaken($year));
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    protected function branchRelations(): array
    {
        return [
            ['user', 'users.branch_id'],
        ];
    }
}
