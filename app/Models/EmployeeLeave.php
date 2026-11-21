<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    use BelongsToBranch;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const TYPES = [
        'casual' => 'Casual Leave',
        'sick' => 'Sick Leave',
        'earned' => 'Earned Leave',
        'unpaid' => 'Unpaid Leave',
        'other' => 'Other',
    ];

    protected $fillable = [
        'institute_id', 'user_id', 'leave_type', 'start_date', 'end_date',
        'days', 'reason', 'status', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'decimal:1',
            'approved_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function dateRangeLabel(): string
    {
        $start = $this->start_date?->format('d M');
        $end = $this->end_date?->format('d M Y');

        return $start && $end ? $start.' â€“ '.$end : (string) $this->start_date;
    }

    protected function branchRelations(): array
    {
        return [
            ['user', 'users.branch_id'],
        ];
    }
}
