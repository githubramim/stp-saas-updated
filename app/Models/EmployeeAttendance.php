<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use BelongsToBranch;

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_HALF_DAY = 'half_day';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LEAVE = 'leave';

    public const STATUSES = [
        self::STATUS_PRESENT => 'Present',
        self::STATUS_LATE => 'Late',
        self::STATUS_HALF_DAY => 'Half Day',
        self::STATUS_ABSENT => 'Absent',
        self::STATUS_LEAVE => 'Leave',
    ];

    protected $fillable = [
        'institute_id', 'user_id', 'date', 'status',
        'check_in', 'check_out', 'remarks', 'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    protected function branchRelations(): array
    {
        return [
            ['user', 'users.branch_id'],
        ];
    }
}
