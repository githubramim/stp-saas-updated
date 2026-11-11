<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const SOURCE_FACEBOOK = 'facebook';

    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_WALK_IN = 'walk_in';

    public const SOURCE_REFERRAL = 'referral';

    public const SOURCE_PHONE = 'phone';

    public const SOURCE_OTHER = 'other';

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_LOST = 'lost';

    public const SOURCES = [
        self::SOURCE_FACEBOOK => 'Facebook',
        self::SOURCE_WEBSITE => 'Website',
        self::SOURCE_WALK_IN => 'Walk-in',
        self::SOURCE_REFERRAL => 'Referral',
        self::SOURCE_PHONE => 'Phone Call',
        self::SOURCE_OTHER => 'Other',
    ];

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_QUALIFIED => 'Qualified',
        self::STATUS_FOLLOW_UP => 'Follow-up',
        self::STATUS_CONVERTED => 'Converted',
        self::STATUS_LOST => 'Lost',
    ];

    /**
     * Statuses a user may pick manually. "Converted" is only set by the
     * convert-to-student action so the student link is always created.
     */
    public const MANUAL_STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_QUALIFIED => 'Qualified',
        self::STATUS_FOLLOW_UP => 'Follow-up',
        self::STATUS_LOST => 'Lost',
    ];

    protected $fillable = [
        'institute_id', 'project_id', 'course_id', 'batch_id', 'assigned_to',
        'converted_student_id', 'name', 'phone', 'email', 'address',
        'source', 'status', 'interest', 'notes', 'lost_reason',
        'last_contacted_at', 'next_follow_up_at', 'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class)->orderBy('scheduled_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LeadMessage::class)->orderBy('sent_at');
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function isOpen(): bool
    {
        return ! $this->isConverted() && ! $this->isLost();
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst((string) $this->source);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'bg-sky-100 text-sky-700',
            self::STATUS_CONTACTED => 'bg-indigo-100 text-indigo-700',
            self::STATUS_QUALIFIED => 'bg-violet-100 text-violet-700',
            self::STATUS_FOLLOW_UP => 'bg-amber-100 text-amber-700',
            self::STATUS_CONVERTED => 'bg-emerald-100 text-emerald-700',
            self::STATUS_LOST => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function sourceIcon(): string
    {
        return match ($this->source) {
            self::SOURCE_FACEBOOK => 'bi-facebook',
            self::SOURCE_WEBSITE => 'bi-globe2',
            self::SOURCE_WALK_IN => 'bi-person-walking',
            self::SOURCE_REFERRAL => 'bi-people',
            self::SOURCE_PHONE => 'bi-telephone',
            default => 'bi-share',
        };
    }

    public function pendingFollowUp(): ?LeadFollowUp
    {
        return $this->followUps
            ->where('status', LeadFollowUp::STATUS_PENDING)
            ->sortBy('scheduled_at')
            ->first();
    }

    /**
     * Follow-up bucket used for badging: overdue, today, upcoming or none.
     */
    public function followUpState(): string
    {
        if (! $this->next_follow_up_at) {
            return 'none';
        }

        if ($this->next_follow_up_at->isPast()) {
            return 'overdue';
        }

        if ($this->next_follow_up_at->isToday()) {
            return 'today';
        }

        return 'upcoming';
    }

    /**
     * Recalculate the cached next follow-up timestamp from pending follow-ups.
     */
    public function syncNextFollowUp(): void
    {
        $next = $this->followUps()
            ->where('status', LeadFollowUp::STATUS_PENDING)
            ->orderBy('scheduled_at')
            ->value('scheduled_at');

        $this->forceFill(['next_follow_up_at' => $next])->save();
    }
}
