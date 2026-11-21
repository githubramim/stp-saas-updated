<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyMaterial extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'course_id', 'batch_id', 'uploaded_by',
        'title', 'description', 'file_path', 'file_type',
        'file_size', 'module_name',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function branchRelations(): array
    {
        return [
            ['batch.project', 'projects.branch_id'],
            ['course.project', 'projects.branch_id'],
            ['uploadedBy', 'users.branch_id'],
        ];
    }
}
