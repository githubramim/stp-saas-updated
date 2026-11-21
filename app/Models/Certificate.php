<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'exam_id', 'certificate_no',
        'title', 'issued_date', 'verification_code',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    protected function branchRelations(): array
    {
        return [
            ['student', 'students.branch_id'],
            ['exam.project', 'projects.branch_id'],
        ];
    }
}
