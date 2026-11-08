<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AttendanceSession extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'institute_id', 'project_id', 'course_id', 'batch_id',
        'title', 'title_bn', 'session_date', 'start_time', 'end_time',
        'late_after_minutes', 'status', 'qr_token', 'qr_expires_at',
        'taken_by', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'late_after_minutes' => 'integer',
            'qr_expires_at' => 'datetime',
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

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function students()
    {
        return Student::where('batch_id', $this->batch_id)
            ->whereIn('status', ['admitted', 'applied'])
            ->orderBy('name_en');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isQrActive(): bool
    {
        return $this->isOpen()
            && $this->qr_token !== null
            && ($this->qr_expires_at === null || $this->qr_expires_at->isFuture());
    }

    public function qrScanUrl(): ?string
    {
        return $this->qr_token ? route('attendance.scan', $this->qr_token) : null;
    }

    /**
     * Decide present/late against the session start time and grace period.
     */
    public function statusFor(?Carbon $at = null): string
    {
        $at ??= Carbon::now();

        if ($this->start_time === null) {
            return Attendance::STATUS_PRESENT;
        }

        $startsAt = Carbon::parse($this->session_date->toDateString().' '.$this->start_time);
        $cutoff = $startsAt->copy()->addMinutes($this->late_after_minutes);

        return $at->greaterThan($cutoff) ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;
    }

    /**
     * Present/late/absent totals for this session.
     */
    public function summary(): array
    {
        $counts = $this->attendances()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $present = (int) $counts->get(Attendance::STATUS_PRESENT, 0);
        $late = (int) $counts->get(Attendance::STATUS_LATE, 0);
        $absent = (int) $counts->get(Attendance::STATUS_ABSENT, 0);

        return [
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'marked' => $present + $late + $absent,
            'total' => $this->students()->count(),
        ];
    }
}
