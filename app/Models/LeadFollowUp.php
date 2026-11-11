<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPES = [
        'call' => 'Phone Call',
        'whatsapp' => 'WhatsApp',
        'sms' => 'SMS',
        'meeting' => 'Meeting',
        'visit' => 'Campus Visit',
        'other' => 'Other',
    ];

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_DONE => 'Done',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'institute_id', 'lead_id', 'user_id', 'type', 'status',
        'scheduled_at', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'call' => 'bi-telephone',
            'whatsapp' => 'bi-whatsapp',
            'sms' => 'bi-chat-dots',
            'meeting' => 'bi-people',
            'visit' => 'bi-building',
            default => 'bi-clock-history',
        };
    }

    public function state(): string
    {
        if (! $this->isPending()) {
            return $this->status;
        }

        if ($this->scheduled_at?->isPast()) {
            return 'overdue';
        }

        if ($this->scheduled_at?->isToday()) {
            return 'today';
        }

        return 'upcoming';
    }
}
