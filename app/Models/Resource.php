<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resource extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'institute_id', 'branch_id', 'resource_name', 'resource_code',
        'location_lab', 'condition', 'description',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_resource')
            ->withPivot('quantity');
    }
}
