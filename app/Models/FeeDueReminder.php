<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeDueReminder extends Model
{
    use BelongsToBranch;

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_PORTAL = 'portal';

    public const CHANNELS = [self::CHANNEL_SMS, self::CHANNEL_PORTAL];

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_FAILED];

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'fee_installment_id',
        'channel', 'due_date', 'status', 'message', 'sent_at', 'response',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'sent_at' => 'datetime',
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

    public function installment(): BelongsTo
    {
        return $this->belongsTo(FeeInstallment::class, 'fee_installment_id');
    }
}
