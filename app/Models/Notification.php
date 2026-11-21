<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'user_id', 'batch_id', 'recipient_phone',
        'message_body', 'type', 'status', 'response',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    protected function branchRelations(): array
    {
        return [
            ['user', 'users.branch_id'],
            ['batch.project', 'projects.branch_id'],
        ];
    }
}
