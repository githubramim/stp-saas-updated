<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'branch_id', 'created_by', 'title', 'body',
        'attachment', 'is_pinned', 'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'expiry_date' => 'date',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString());
        });
    }

    protected function branchRelations(): array
    {
        return [
            ['creator', 'users.branch_id'],
        ];
    }
}
