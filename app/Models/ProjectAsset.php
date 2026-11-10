<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAsset extends Model
{
    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_DISTRIBUTED = 'distributed';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'institute_id', 'project_id', 'student_id', 'name', 'category',
        'asset_code', 'quantity', 'unit_cost', 'distribution_date',
        'return_date', 'status', 'condition', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'distribution_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_IN_STOCK => 'In Stock',
            self::STATUS_DISTRIBUTED => 'Distributed',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_DAMAGED => 'Damaged',
            self::STATUS_LOST => 'Lost',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
