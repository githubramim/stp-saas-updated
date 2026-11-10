<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'institute_id', 'project_id', 'course_id', 'batch_id',
        'name_en', 'name_bn', 'father_name_en', 'father_name_bn',
        'mother_name_en', 'mother_name_bn', 'contact', 'email',
        'dob', 'gender', 'religion', 'nationality', 'blood_group',
        'id_type', 'id_no', 'occupation', 'ntvqf_level', 'guardian_contact', 'guardian_relation',
        'present_village', 'present_road', 'present_po', 'present_upazila',
        'present_district', 'present_division',
        'perm_village', 'perm_road', 'perm_po', 'perm_upazila',
        'perm_district', 'perm_division',
        'edu_degree', 'edu_institute', 'edu_year', 'edu_cgpa', 'edu_address',
        'photo_path', 'signature_path', 'nid_path',
        'status', 'reference_no',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'edu_year' => 'integer',
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

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function stipends(): HasMany
    {
        return $this->hasMany(Stipend::class);
    }

    public function projectAssets(): HasMany
    {
        return $this->hasMany(ProjectAsset::class);
    }

    public function jobPlacement(): HasMany
    {
        return $this->hasMany(JobPlacement::class);
    }

    /**
     * Attendance tally for a batch (defaults to the student's own batch).
     * Late arrivals count as attended, which is the norm for funded projects.
     */
    public function attendanceSummary(?int $batchId = null): array
    {
        $batchId ??= $this->batch_id;

        $rows = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->where('attendances.student_id', $this->id)
            ->when($batchId, fn ($q) => $q->where('attendance_sessions.batch_id', $batchId))
            ->selectRaw('attendances.status, count(*) as total')
            ->groupBy('attendances.status')
            ->pluck('total', 'status');

        $present = (int) $rows->get(Attendance::STATUS_PRESENT, 0);
        $late = (int) $rows->get(Attendance::STATUS_LATE, 0);
        $absent = (int) $rows->get(Attendance::STATUS_ABSENT, 0);
        $total = $present + $late + $absent;

        return [
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'total' => $total,
            'percentage' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0.0,
        ];
    }

    public function cgpa(?int $instituteId = null): float
    {
        return ExamResult::cgpaFor($this->id, $instituteId ?? $this->institute_id);
    }
}
