<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySheet extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'institute_id', 'user_id', 'month', 'basic_salary', 'allowance',
        'deduction', 'net_salary', 'status', 'paid_on', 'transaction_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SalarySheet $sheet) {
            $sheet->net_salary = round(
                (float) $sheet->basic_salary + (float) $sheet->allowance - (float) $sheet->deduction,
                2
            );
        });
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function monthLabel(): string
    {
        try {
            return \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
        } catch (\Throwable) {
            return (string) $this->month;
        }
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }
}
