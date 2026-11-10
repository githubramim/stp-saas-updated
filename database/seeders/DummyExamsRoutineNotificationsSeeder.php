<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Institute;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds demo exams, class routines and notifications for the existing batches.
 *
 * Safe to run repeatedly: every record is matched on its natural key before
 * being created, so no duplicates are produced.
 */
class DummyExamsRoutineNotificationsSeeder extends Seeder
{
    /**
     * Ten exam plans spread round-robin across the institute's batches.
     *
     * @var array<int, array{title: string, type: string, days: int, published: bool}>
     */
    private array $examPlans = [
        ['title' => 'Demo Midterm Assessment', 'type' => Exam::TYPE_MCQ, 'days' => -20, 'published' => true],
        ['title' => 'Demo Practical Skills Test', 'type' => Exam::TYPE_PRACTICAL, 'days' => -10, 'published' => true],
        ['title' => 'Demo Final Written Examination', 'type' => Exam::TYPE_WRITTEN, 'days' => -3, 'published' => true],
        ['title' => 'Demo Occupational Safety Quiz', 'type' => Exam::TYPE_MCQ, 'days' => 5, 'published' => false],
        ['title' => 'Demo Trade Practical Assessment', 'type' => Exam::TYPE_PRACTICAL, 'days' => 12, 'published' => false],
        ['title' => 'Demo Unit Test — Theory', 'type' => Exam::TYPE_WRITTEN, 'days' => 18, 'published' => false],
        ['title' => 'Demo Monthly MCQ Test', 'type' => Exam::TYPE_MCQ, 'days' => 25, 'published' => false],
        ['title' => 'Demo Workplace Simulation', 'type' => Exam::TYPE_PRACTICAL, 'days' => 32, 'published' => false],
        ['title' => 'Demo Final Assessment — Written', 'type' => Exam::TYPE_WRITTEN, 'days' => 40, 'published' => false],
        ['title' => 'Demo Skill Verification Test', 'type' => Exam::TYPE_PRACTICAL, 'days' => 48, 'published' => false],
    ];

    /**
     * @var array<int, string>
     */
    private array $notificationMessages = [
        'Your class starts tomorrow at 9:00 AM. Please arrive on time.',
        'The demo exam schedule has been published. Check the Exams page for details.',
        'Stipend disbursement is scheduled this week. Bring your trainee ID card.',
        'Attendance below 80% may affect your certification. Please attend regularly.',
        'The practical assessment will be held in the lab. Wear your safety equipment.',
        'Your admission is confirmed. Collect your trainee ID card from the office.',
        'Your monthly progress report is ready. Contact your trainer for feedback.',
        'Job placement orientation is on Sunday at 10:00 AM in the seminar room.',
        'Please submit a copy of your NID for verification.',
        'Congratulations on completing the module. Keep up the good work.',
    ];

    public function run(): void
    {
        $instituteId = User::query()->whereNotNull('institute_id')->value('institute_id')
            ?? Institute::query()->value('id');

        if (! $instituteId) {
            $this->command?->error('No institute found. Seed an institute/user first.');

            return;
        }

        $batches = Batch::query()
            ->where('institute_id', $instituteId)
            ->orderBy('id')
            ->get();

        if ($batches->isEmpty()) {
            $this->command?->error('No batches found. Run DummyProjectTrainingSeeder first.');

            return;
        }

        $adminId = User::query()
            ->where('institute_id', $instituteId)
            ->where('role', 'admin')
            ->value('id');

        $exams = $this->seedExams($instituteId, $adminId, $batches);
        $routines = $this->seedRoutines($instituteId, $batches);
        $notifications = $this->seedNotifications($instituteId, $adminId);

        $this->command?->info(sprintf(
            'Dummy exams/routine/notifications ready: %d exams, %d class slots, %d notifications.',
            $exams,
            $routines,
            $notifications
        ));
    }

    private function seedExams(int $instituteId, ?int $adminId, $batches): int
    {
        $created = 0;

        foreach ($this->examPlans as $index => $plan) {
            $batch = $batches[$index % $batches->count()];

            $exam = Exam::firstOrCreate(
                [
                    'institute_id' => $instituteId,
                    'batch_id' => $batch->id,
                    'title' => $plan['title'],
                ],
                [
                    'project_id' => $batch->project_id,
                    'course_id' => $batch->course_id,
                    'exam_type' => $plan['type'],
                    'exam_date' => now()->addDays($plan['days'])->toDateString(),
                    'duration_minutes' => $plan['type'] === Exam::TYPE_MCQ ? 60 : 90,
                    'instructions' => 'Demo exam generated for the project-based training module.',
                    'status' => $plan['published'] ? Exam::STATUS_PUBLISHED : Exam::STATUS_DRAFT,
                    'published_at' => $plan['published'] ? now()->subDays(2) : null,
                    'created_by' => $adminId,
                ]
            );

            if ($exam->wasRecentlyCreated) {
                $created++;
            }

            $this->seedQuestions($exam, $plan['type']);

            $total = (float) $exam->questions()->sum('marks');
            $exam->update([
                'total_marks' => $total,
                'pass_marks' => round($total * 0.4, 2),
            ]);
        }

        return $created;
    }

    private function seedQuestions(Exam $exam, string $type): void
    {
        $questions = match ($type) {
            Exam::TYPE_MCQ => [
                [
                    'question' => 'Which of the following is the most important part of workplace safety?',
                    'options' => ['Using PPE correctly', 'Skipping the safety briefing', 'Working without supervision', 'Ignoring warning signs'],
                    'correct' => 'a',
                    'marks' => 2,
                ],
                [
                    'question' => 'What should you do before starting a practical task?',
                    'options' => ['Check tools and equipment', 'Start immediately', 'Leave tools on the floor', 'Borrow unknown tools'],
                    'correct' => 'a',
                    'marks' => 2,
                ],
                [
                    'question' => 'Which document records daily work activities?',
                    'options' => ['Job card / logbook', 'Newspaper', 'Invoice only', 'Visitor pass'],
                    'correct' => 'a',
                    'marks' => 2,
                ],
                [
                    'question' => 'How should waste materials be handled?',
                    'options' => ['Segregate and dispose safely', 'Burn them inside the workplace', 'Leave them at the workstation', 'Mix them with drinking water'],
                    'correct' => 'a',
                    'marks' => 2,
                ],
                [
                    'question' => 'Who should you report a workplace accident to?',
                    'options' => ['Supervisor / safety officer', 'A friend outside the institute', 'No one', 'The next customer'],
                    'correct' => 'a',
                    'marks' => 2,
                ],
            ],
            Exam::TYPE_WRITTEN => [
                [
                    'question' => 'Describe the key safety precautions required for your trade.',
                    'marks' => 10,
                ],
                [
                    'question' => 'Explain the steps of the practical task demonstrated in class.',
                    'marks' => 10,
                ],
            ],
            default => [
                [
                    'question' => 'Demonstrate the core practical task for this occupation and explain each step to the assessor.',
                    'marks' => 20,
                ],
            ],
        };

        foreach ($questions as $order => $question) {
            ExamQuestion::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'display_order' => $order + 1,
                ],
                [
                    'question_type' => $type,
                    'question' => $question['question'],
                    'option_a' => $question['options'][0] ?? null,
                    'option_b' => $question['options'][1] ?? null,
                    'option_c' => $question['options'][2] ?? null,
                    'option_d' => $question['options'][3] ?? null,
                    'correct_option' => $question['correct'] ?? null,
                    'marks' => $question['marks'],
                    'negative_marks' => 0,
                ]
            );
        }
    }

    private function seedRoutines(int $instituteId, $batches): int
    {
        $days = [0, 2, 4]; // Saturday, Monday, Wednesday (Bangladesh week)
        $topics = ['Theory', 'Practical', 'Assessment'];
        $created = 0;

        foreach ($batches as $batch) {
            $trainer = $this->ensureTrainer($instituteId, $batch);

            if (! $batch->trainer_id) {
                $batch->update(['trainer_id' => $trainer->id]);
            }

            $evening = strcasecmp((string) $batch->shift, 'Evening') === 0;
            $start = $evening ? '18:00' : '09:00';
            $end = $evening ? '21:00' : '12:00';
            $room = ($evening ? 'Lab ' : 'Room ').$batch->id;

            foreach ($days as $slot => $day) {
                $schedule = Schedule::firstOrCreate(
                    [
                        'institute_id' => $instituteId,
                        'batch_id' => $batch->id,
                        'day_of_week' => $day,
                        'start_time' => $start,
                    ],
                    [
                        'project_id' => $batch->project_id,
                        'end_time' => $end,
                        'room' => $room,
                        'trainer_id' => $trainer->id,
                        'subject' => ($batch->course?->name ?? 'Trade').' — '.$topics[$slot % count($topics)],
                        'status' => 'active',
                        'remarks' => 'Demo class routine slot.',
                    ]
                );

                if ($schedule->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        return $created;
    }

    private function ensureTrainer(int $instituteId, Batch $batch): User
    {
        if ($batch->trainer_id) {
            $existing = User::find($batch->trainer_id);

            if ($existing) {
                return $existing;
            }
        }

        return User::firstOrCreate(
            ['email' => 'trainer.demo.'.$batch->id.'@demo.com'],
            [
                'name' => 'Demo Trainer — '.$batch->name,
                'password' => Hash::make('password123'),
                'phone' => '019'.str_pad((string) $batch->id, 8, '0', STR_PAD_LEFT),
                'role' => 'trainer',
                'institute_id' => $instituteId,
            ]
        );
    }

    private function seedNotifications(int $instituteId, ?int $adminId): int
    {
        $students = Student::query()
            ->where('institute_id', $instituteId)
            ->orderBy('id')
            ->limit(count($this->notificationMessages))
            ->get();

        if ($students->isEmpty()) {
            return 0;
        }

        $created = 0;

        foreach ($this->notificationMessages as $index => $message) {
            $student = $students[$index % $students->count()];

            $notification = Notification::firstOrCreate(
                [
                    'institute_id' => $instituteId,
                    'batch_id' => $student->batch_id,
                    'recipient_phone' => $student->contact,
                    'message_body' => $message,
                ],
                [
                    'user_id' => $adminId,
                    'type' => $index % 2 === 0 ? 'sms' : 'system_alert',
                    'status' => 'sent',
                    'response' => 'Seeded demo notification.',
                ]
            );

            if ($notification->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
