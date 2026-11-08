<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\Project;
use App\Models\Course;
use App\Models\Batch;
use App\Models\User;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Transaction;
use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $institute = Institute::first() ?? Institute::create([
            'name' => 'Demo Training Institute',
            'code' => 'DEMO01',
            'mobile' => '01700000000',
            'email' => 'institute@demo.com',
            'status' => 'active',
        ]);

        $projectConfigs = [
            [
                'code' => 'ASSES',
                'name' => 'ASSES Skills Development Project',
                'courses' => [
                    [
                        'name' => 'Web Development & Database Administration',
                        'code' => 'WD-101',
                        'ntvqf' => 'NTVQF Level 3',
                        'hours' => 360,
                        'batches' => ['WD-Batch-01', 'WD-Batch-02'],
                    ],
                    [
                        'name' => 'Graphic Design & Multimedia',
                        'code' => 'GD-102',
                        'ntvqf' => 'NTVQF Level 2',
                        'hours' => 240,
                        'batches' => ['GD-Batch-01', 'GD-Batch-02'],
                    ],
                ],
            ],
            [
                'code' => 'NHRDF',
                'name' => 'NHRDF Technical Skill Enhancement',
                'courses' => [
                    [
                        'name' => 'Electrical Installation & Maintenance',
                        'code' => 'ELE-201',
                        'ntvqf' => 'NTVQF Level 3',
                        'hours' => 360,
                        'batches' => ['ELE-Batch-01', 'ELE-Batch-02'],
                    ],
                    [
                        'name' => 'Plumbing & Pipe Fitting',
                        'code' => 'PLM-202',
                        'ntvqf' => 'NTVQF Level 2',
                        'hours' => 200,
                        'batches' => ['PLM-Batch-01', 'PLM-Batch-02'],
                    ],
                ],
            ],
            [
                'code' => 'ISEC',
                'name' => 'ISEC Digital Literacy & IT Training',
                'courses' => [
                    [
                        'name' => 'Digital Marketing & E-Commerce',
                        'code' => 'DM-301',
                        'ntvqf' => 'NTVQF Level 3',
                        'hours' => 300,
                        'batches' => ['DM-Batch-01', 'DM-Batch-02'],
                    ],
                    [
                        'name' => 'Refrigeration & Air Conditioning',
                        'code' => 'RAC-302',
                        'ntvqf' => 'NTVQF Level 2',
                        'hours' => 240,
                        'batches' => ['RAC-Batch-01', 'RAC-Batch-02'],
                    ],
                ],
            ],
        ];

        $firstNames = ['Tanvir', 'Rahim', 'Naimur', 'Fahim', 'Mehedi', 'Sabbir', 'Nusrat', 'Sumaiya', 'Sadia', 'Farhana', 'Tariqul', 'Imran', 'Kamrul', 'Shakil', 'Rifat', 'Mahfuz', 'Ayesha', 'Fatema', 'Sharmin', 'Rubel'];
        $lastNames = ['Hassan', 'Rahman', 'Islam', 'Ahmed', 'Chowdhury', 'Khan', 'Jahan', 'Akter', 'Sultana', 'Hossain', 'Alam', 'Mahmud', 'Uddin', 'Miah', 'Sarker'];
        $divisions = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh'];
        $religions = ['Islam', 'Hinduism', 'Buddhism', 'Christianity'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        $createdBatches = [];
        $createdTrainers = [];

        foreach ($projectConfigs as $pConfig) {
            $project = Project::firstOrCreate(
                ['institute_id' => $institute->id, 'code' => $pConfig['code']],
                ['name' => $pConfig['name'], 'status' => 'active']
            );

            // Create Trainers for Project and Occupations
            foreach ($pConfig['courses'] as $cIndex => $cConfig) {
                $trainer1 = User::firstOrCreate(
                    ['email' => strtolower($pConfig['code']) . '.trainer' . ($cIndex * 2 + 1) . '@stp.com'],
                    [
                        'name' => 'Trainer ' . $cConfig['code'] . ' Lead',
                        'password' => Hash::make('trainer123'),
                        'phone' => '017' . rand(10000000, 99999999),
                        'role' => 'trainer',
                        'institute_id' => $institute->id,
                    ]
                );

                $trainer2 = User::firstOrCreate(
                    ['email' => strtolower($pConfig['code']) . '.trainer' . ($cIndex * 2 + 2) . '@stp.com'],
                    [
                        'name' => 'Trainer ' . $cConfig['code'] . ' Associate',
                        'password' => Hash::make('trainer123'),
                        'phone' => '018' . rand(10000000, 99999999),
                        'role' => 'trainer',
                        'institute_id' => $institute->id,
                    ]
                );

                $createdTrainers[] = $trainer1;
                $createdTrainers[] = $trainer2;

                // Create Course
                $course = Course::firstOrCreate(
                    ['institute_id' => $institute->id, 'course_code' => $cConfig['code']],
                    [
                        'project_id' => $project->id,
                        'name' => $cConfig['name'],
                        'ntvqf_level' => $cConfig['ntvqf'],
                        'duration_hours' => $cConfig['hours'],
                        'status' => 'active',
                    ]
                );

                // Create 2 Batches per Course & assign Trainers
                foreach ($cConfig['batches'] as $bIndex => $bName) {
                    $assignedTrainer = ($bIndex % 2 === 0) ? $trainer1 : $trainer2;
                    $batch = Batch::firstOrCreate(
                        ['institute_id' => $institute->id, 'name' => $bName],
                        [
                            'project_id' => $project->id,
                            'course_id' => $course->id,
                            'trainer_id' => $assignedTrainer->id,
                            'start_date' => now()->subMonths(2)->toDateString(),
                            'end_date' => now()->addMonths(2)->toDateString(),
                            'shift' => ($bIndex % 2 === 0) ? 'Morning' : 'Afternoon',
                            'seat_capacity' => 30,
                            'status' => 'running',
                        ]
                    );
                    $createdBatches[] = ['project' => $project, 'course' => $course, 'batch' => $batch];
                }
            }
        }

        // Generate 100 Students across projects and batches
        for ($i = 1; $i <= 100; $i++) {
            $batchInfo = $createdBatches[($i - 1) % count($createdBatches)];
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $gender = ($i % 3 == 0) ? 'Female' : 'Male';
            $status = ($i <= 70) ? 'admitted' : (($i <= 90) ? 'applied' : 'rejected');

            $student = Student::create([
                'institute_id' => $institute->id,
                'project_id' => $batchInfo['project']->id,
                'course_id' => $batchInfo['course']->id,
                'batch_id' => $batchInfo['batch']->id,
                'name_en' => "$fn $ln",
                'name_bn' => "$fn $ln",
                'father_name_en' => "Father of $fn",
                'mother_name_en' => "Mother of $fn",
                'contact' => '01' . rand(3, 9) . rand(10000000, 99999999),
                'email' => strtolower($fn) . '.' . strtolower($ln) . $i . '@example.com',
                'dob' => now()->subYears(rand(18, 26))->format('Y-m-d'),
                'gender' => $gender,
                'religion' => $religions[array_rand($religions)],
                'nationality' => 'Bangladeshi',
                'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                'id_type' => 'NID',
                'id_no' => rand(1000000000, 9999999999),
                'guardian_contact' => '017' . rand(10000000, 99999999),
                'guardian_relation' => 'Father',
                'present_district' => $divisions[array_rand($divisions)],
                'status' => $status,
                'reference_no' => 'STP-' . strtoupper(Str::random(8)),
            ]);

            // Add Attendance for Admitted Students
            if ($status === 'admitted') {
                $isLowAttendanceStudent = ($i % 12 === 0); // Create a few low attendance cases for dashboard alert test
                for ($d = 20; $d >= 1; $d--) {
                    $attDate = now()->subDays($d)->toDateString();
                    $isPresent = $isLowAttendanceStudent ? ($d % 4 === 0) : ($d % 8 !== 0);
                    Attendance::updateOrCreate(
                        [
                            'batch_id' => $batchInfo['batch']->id,
                            'student_id' => $student->id,
                            'attendance_date' => $attDate,
                        ],
                        [
                            'institute_id' => $institute->id,
                            'marked_by' => $batchInfo['batch']->trainer_id,
                            'is_present' => $isPresent,
                        ]
                    );
                }

                // Add Fee Payment Transaction
                Transaction::create([
                    'institute_id' => $institute->id,
                    'student_id' => $student->id,
                    'batch_id' => $batchInfo['batch']->id,
                    'type' => 'income',
                    'category' => 'Admission Fee',
                    'amount' => rand(1500, 3000),
                    'transaction_date' => now()->subDays(rand(1, 45))->toDateString(),
                    'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                    'payment_method' => 'bKash',
                    'description' => 'Course admission fee collected',
                ]);
            }
        }

        // Create Monthly Expenses & Additional Transactions
        for ($m = 5; $m >= 0; $m--) {
            $tDate = now()->subMonths($m)->startOfMonth()->addDays(5)->toDateString();
            Transaction::create([
                'institute_id' => $institute->id,
                'type' => 'expense',
                'category' => 'Trainer Salary',
                'amount' => rand(25000, 40000),
                'transaction_date' => $tDate,
                'description' => 'Monthly honorarium for certified trainers',
            ]);

            Transaction::create([
                'institute_id' => $institute->id,
                'type' => 'expense',
                'category' => 'Utility & Lab Maintenance',
                'amount' => rand(8000, 15000),
                'transaction_date' => $tDate,
                'description' => 'Internet, Electricity and hardware repair bills',
            ]);
        }

        // Create Lab Resources
        Resource::firstOrCreate(
            ['institute_id' => $institute->id, 'resource_code' => 'PC-LAB1-01'],
            ['resource_name' => 'Core i5 Workstation PC', 'location_lab' => 'Lab-1 (Software)', 'condition' => 'good']
        );
        Resource::firstOrCreate(
            ['institute_id' => $institute->id, 'resource_code' => 'PC-LAB1-02'],
            ['resource_name' => 'Core i7 Graphics PC', 'location_lab' => 'Lab-1 (Software)', 'condition' => 'damaged']
        );
        Resource::firstOrCreate(
            ['institute_id' => $institute->id, 'resource_code' => 'RTR-LAB2-01'],
            ['resource_name' => 'Cisco Router 2901', 'location_lab' => 'Lab-2 (Hardware)', 'condition' => 'under_maintenance']
        );
    }
}
