<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'institute_id', 'name', 'name_bn', 'code', 'description', 'status',
        'donor_name', 'donor_type', 'budget_amount', 'mou_number', 'mou_file_path',
        'start_date', 'end_date', 'target_trainees', 'coordinator_name', 'coordinator_contact',
    ];

    protected function casts(): array
    {
        return [
            'budget_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'target_trainees' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function stipends(): HasMany
    {
        return $this->hasMany(Stipend::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ProjectAsset::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(JobPlacement::class);
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Student::class)
            ->whereIn('status', ['applied', 'shortlisted', 'selected', 'admitted', 'active', 'completed']);
    }
}
