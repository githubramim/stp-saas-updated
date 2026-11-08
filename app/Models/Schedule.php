<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    /**
     * Bangladesh weekly calendar: the week starts on Saturday.
     * 0=Saturday, 1=Sunday, 2=Monday, 3=Tuesday, 4=Wednesday, 5=Thursday, 6=Friday.
     */
    public const DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    protected $fillable = [
        'institute_id', 'project_id', 'batch_id', 'day_of_week',
        'start_time', 'end_time', 'room', 'trainer_id', 'subject',
        'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function dayName(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * "09:00 AM – 11:00 AM"
     */
    public function timeRange(): string
    {
        return $this->start_time.' – '.$this->end_time;
    }

    /**
     * Does this slot overlap another slot on the same day?
     */
    public function overlaps(Schedule $other): bool
    {
        return $this->id !== $other->id
            && $this->day_of_week === $other->day_of_week
            && $this->start_time < $other->end_time
            && $other->start_time < $this->end_time;
    }

    /**
     * Two slots clash only when the overlap matters:
     * same room, same trainer, or same batch at the same time.
     */
    public function clashes(Schedule $other): bool
    {
        if (! $this->overlaps($other)) {
            return false;
        }

        return ($this->room && $this->room === $other->room)
            || ($this->trainer_id && $this->trainer_id === $other->trainer_id)
            || $this->batch_id === $other->batch_id;
    }
}