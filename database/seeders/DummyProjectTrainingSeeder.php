<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use App\Services\TamListService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Creates TAM-linked demo courses, batches and students for every project.
 *
 * Each project receives two courses taken from the TAM List, two batches per
 * course (Morning / Evening) and ten trainees per batch — 40 students in total.
 * The seeder is idempotent, so it can be re-run safely.
 */
class DummyProjectTrainingSeeder extends Seeder
{
    private array $maleNames = [
        'Md. Rakib Hasan', 'Abul Kalam', 'Shakil Ahmed', 'Jahid Hossain', 'Sabbir Rahman',
        'Nayeem Islam', 'Tariqul Islam', 'Mehedi Hasan', 'Arif Billah', 'Sohel Rana',
    ];

    private array $femaleNames = [
        'Fatema Akter', 'Sumaiya Khatun', 'Nusrat Jahan', 'Ayesha Siddika', 'Rina Begum',
        'Jannatul Ferdous', 'Sharmin Akter', 'Mariam Akter', 'Taslima Nasrin', 'Rokeya Sultana',
    ];

    public function run(): void
    {
        $instituteId = User::query()->whereNotNull('institute_id')->value('institute_id')
            ?? Institute::query()->value('id');

        if (! $instituteId) {
            $this->command?->error('No institute found. Seed an institute/user first.');

            return;
        }

        $projects = Project::query()->orderBy('id')->get();

        if ($projects->isEmpty()) {
            $this->command?->error('No projects found. Create a project first.');

            return;
        }

        $entries = $this->tamEntries();

        if ($entries->isEmpty()) {
            $entries = collect($this->fallbackEntries());
        }

        $courseCount = 0;
        $batchCount = 0;
        $studentCount = 0;

        foreach ($projects as $projectIndex => $project) {
            $projectEntries = $entries->slice($projectIndex * 2, 2)->values();

            foreach ($projectEntries as $entryIndex => $entry) {
                $hours = (int) $entry['duration'];
                $occupation = $entry['occupation'];
                $level = (string) $entry['level'];

                $course = Course::firstOrCreate(
                    [
                        'institute_id' => $instituteId,
                        'project_id' => $project->id,
                        'name' => $occupation,
                        'ntvqf_level' => $level,
                    ],
                    [
                        'course_code' => sprintf('DEMO-%02d-%02d', $project->id, $entryIndex + 1),
                        'tam_sector' => $entry['sector'],
                        'tam_occupation' => $occupation,
                        'duration' => $hours.' hours',
                        'duration_hours' => $hours,
                        'fee' => 0,
                        'seat_capacity' => 30,
                        'description' => 'Demo course linked to a TAM occupational standard.',
                        'status' => 'active',
                    ]
                );
                $courseCount++;

                foreach (['Morning', 'Evening'] as $shiftIndex => $shift) {
                    $batch = Batch::firstOrCreate(
                        [
                            'institute_id' => $instituteId,
                            'project_id' => $project->id,
                            'course_id' => $course->id,
                            'name' => $course->name.' — '.$shift,
                        ],
                        [
                            'start_date' => now()->subDays(30)->toDateString(),
                            'end_date' => now()->addMonths(4)->toDateString(),
                            'shift' => $shift,
                            'seat_capacity' => 30,
                            'status' => 'active',
                        ]
                    );
                    $batchCount++;

                    $studentCount += $this->seedStudents($instituteId, $project, $course, $batch, $shiftIndex);
                }
            }
        }

        $this->command?->info(sprintf(
            'Dummy PBTM data ready: %d courses, %d batches, %d students.',
            $courseCount,
            $batchCount,
            $studentCount
        ));
    }

    /**
     * One TAM row per distinct Occupation + Level, preserving all durations.
     */
    private function tamEntries(): Collection
    {
        $rows = app(TamListService::class)->rows();

        return collect($rows)
            ->groupBy(fn (array $row) => mb_strtolower($row['occupation']).'|'.$row['level'])
            ->map(function (Collection $group) {
                $entry = $group->first();
                $entry['durations'] = $group->pluck('duration')->unique()->sort()->values()->all();

                return $entry;
            })
            ->values();
    }

    private function seedStudents(int $instituteId, Project $project, Course $course, Batch $batch, int $shiftIndex): int
    {
        $statuses = ['applied', 'shortlisted', 'selected', 'admitted', 'active', 'active', 'active', 'active', 'active', 'completed'];
        $bloodGroups = ['A+', 'B+', 'O+', 'AB+'];
        $created = 0;

        for ($i = 1; $i <= 10; $i++) {
            $isFemale = $i % 2 === 0;
            $names = $isFemale ? $this->femaleNames : $this->maleNames;
            $name = $names[($i + $shiftIndex) % count($names)];
            $reference = sprintf(
                'DEMO-%s-%d-%d-%02d',
                $project->code ?: $project->id,
                $course->id,
                $batch->id,
                $i
            );

            $student = Student::firstOrCreate(
                [
                    'institute_id' => $instituteId,
                    'reference_no' => $reference,
                ],
                [
                    'project_id' => $project->id,
                    'course_id' => $course->id,
                    'batch_id' => $batch->id,
                    'name_en' => $name,
                    'father_name_en' => 'Md. Abdul Karim',
                    'mother_name_en' => 'Mrs. Rahima Begum',
                    'contact' => $this->phone('017', $reference),
                    'email' => strtolower(preg_replace('/[^a-z0-9]+/i', '.', $name)).'.'.$course->id.$batch->id.$i.'@example.com',
                    'dob' => Carbon::create(1998 + ($i % 6), (($i * 3) % 12) + 1, (($i * 7) % 27) + 1)->toDateString(),
                    'gender' => $isFemale ? 'Female' : 'Male',
                    'religion' => 'Islam',
                    'nationality' => 'Bangladeshi',
                    'blood_group' => $bloodGroups[$i % count($bloodGroups)],
                    'id_type' => 'nid',
                    'id_no' => 'NID-DEMO-'.$project->id.'-'.$course->id.'-'.$batch->id.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'occupation' => $course->tam_occupation,
                    'ntvqf_level' => (string) $course->ntvqf_level,
                    'guardian_contact' => $this->phone('018', $reference.'-guardian'),
                    'guardian_relation' => $isFemale ? 'Mother' : 'Father',
                    'present_village' => 'Demo Village',
                    'present_road' => 'Demo Road',
                    'present_po' => 'Demo PO',
                    'present_upazila' => 'Demo Upazila',
                    'present_district' => 'Dhaka',
                    'present_division' => 'Dhaka',
                    'perm_village' => 'Demo Village',
                    'perm_road' => 'Demo Road',
                    'perm_po' => 'Demo PO',
                    'perm_upazila' => 'Demo Upazila',
                    'perm_district' => 'Dhaka',
                    'perm_division' => 'Dhaka',
                    'edu_degree' => 'SSC',
                    'edu_institute' => 'Demo High School',
                    'edu_year' => 2018 + ($i % 4),
                    'edu_cgpa' => 3.0 + (($i % 10) / 10),
                    'edu_address' => 'Demo Address',
                    'status' => $statuses[$i - 1],
                ]
            );

            if ($student->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function phone(string $prefix, string $seed): string
    {
        $number = abs(crc32($seed)) % 100000000;

        return $prefix.str_pad((string) $number, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<int, array{sector: string, occupation: string, level: int, duration: int}>
     */
    private function fallbackEntries(): array
    {
        return [
            ['sector' => 'Construction', 'occupation' => 'Foundation Work', 'level' => 3, 'duration' => 575],
            ['sector' => 'Construction', 'occupation' => 'Electrical Installation and Maintenance for Construction', 'level' => 2, 'duration' => 360],
            ['sector' => 'Information and Communication Technology', 'occupation' => 'Search Engine Optimization', 'level' => 4, 'duration' => 725],
            ['sector' => 'Information and Communication Technology', 'occupation' => 'Graphic Design', 'level' => 3, 'duration' => 360],
            ['sector' => 'RMG & Textile', 'occupation' => 'Safety Management for RMG', 'level' => 3, 'duration' => 360],
            ['sector' => 'Light Engineering', 'occupation' => 'Automotive Mechanics', 'level' => 4, 'duration' => 360],
        ];
    }
}
