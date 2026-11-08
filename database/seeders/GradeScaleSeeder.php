<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use Illuminate\Database\Seeder;

/**
 * Default BD/NTG-style grading scale. Rows with a null institute_id act as the
 * shared fallback; an institute may override by inserting its own bands.
 */
class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        $bands = [
            ['min_percentage' => 80, 'max_percentage' => 100, 'grade_point' => 5.00, 'grade_letter' => 'A+', 'remark' => 'Outstanding'],
            ['min_percentage' => 75, 'max_percentage' => 79.99, 'grade_point' => 5.00, 'grade_letter' => 'A', 'remark' => 'Excellent'],
            ['min_percentage' => 70, 'max_percentage' => 74.99, 'grade_point' => 4.50, 'grade_letter' => 'B+', 'remark' => 'Very Good'],
            ['min_percentage' => 65, 'max_percentage' => 69.99, 'grade_point' => 4.00, 'grade_letter' => 'B', 'remark' => 'Good'],
            ['min_percentage' => 60, 'max_percentage' => 64.99, 'grade_point' => 3.50, 'grade_letter' => 'C+', 'remark' => 'Satisfactory'],
            ['min_percentage' => 55, 'max_percentage' => 59.99, 'grade_point' => 3.00, 'grade_letter' => 'C', 'remark' => 'Fair'],
            ['min_percentage' => 50, 'max_percentage' => 54.99, 'grade_point' => 2.50, 'grade_letter' => 'D+', 'remark' => 'Weak'],
            ['min_percentage' => 45, 'max_percentage' => 49.99, 'grade_point' => 2.00, 'grade_letter' => 'D', 'remark' => 'Poor'],
            ['min_percentage' => 0, 'max_percentage' => 44.99, 'grade_point' => 0.00, 'grade_letter' => 'F', 'remark' => 'Fail'],
        ];

        foreach ($bands as $band) {
            GradeScale::updateOrCreate(
                ['institute_id' => null, 'min_percentage' => $band['min_percentage']],
                $band
            );
        }
    }
}
