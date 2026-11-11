<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\LeadMessage;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic Mini CRM pipeline: 28 leads across every source and status,
 * linked converted leads, follow-up reminders (overdue, today and upcoming) and
 * WhatsApp history. Safe to run repeatedly — leads are keyed by phone number.
 */
class DummyCrmSeeder extends Seeder
{
    private array $names = [
        'Rakibul Hasan', 'Sumaiya Akter', 'Md. Arif Hossain', 'Nusrat Jahan',
        'Sabbir Ahmed', 'Tanjila Rahman', 'Jahidul Islam', 'Farhana Yasmin',
        'Mehedi Hasan', 'Ayesha Siddika', 'Shakil Khan', 'Rabeya Begum',
        'Naimur Rahman', 'Mst. Rina Khatun', 'Fahim Faisal', 'Sadia Afrin',
        'Tanvir Alam', 'Jannatul Ferdous', 'Rasel Mahmud', 'Mariya Sultana',
        'Sohel Rana', 'Nazmul Huda', 'Sharmin Akter', 'Imran Kabir',
        'Tahmina Akter', 'Ashiqur Rahman', 'Lima Akter', 'Sajib Das',
    ];

    private array $statuses = [
        'new', 'new', 'new', 'new', 'new', 'new',
        'contacted', 'contacted', 'contacted', 'contacted', 'contacted',
        'qualified', 'qualified', 'qualified', 'qualified',
        'follow_up', 'follow_up', 'follow_up', 'follow_up', 'follow_up',
        'converted', 'converted', 'converted', 'converted', 'converted',
        'lost', 'lost', 'lost',
    ];

    private array $sources = [
        'facebook', 'website', 'walk_in', 'referral', 'phone', 'other',
        'facebook', 'facebook', 'website', 'walk_in', 'referral',
        'facebook', 'website', 'phone', 'referral',
        'facebook', 'website', 'walk_in', 'phone', 'other',
        'facebook', 'website', 'referral', 'walk_in', 'phone',
        'facebook', 'website', 'other',
    ];

    public function run(): void
    {
        $instituteId = User::query()->whereNotNull('institute_id')->value('institute_id')
            ?? Institute::query()->value('id');

        if (! $instituteId) {
            $this->command?->error('No institute found. Seed an institute/user first.');

            return;
        }

        $courses = Course::query()->where('institute_id', $instituteId)->orderBy('id')->get();
        $batches = Batch::query()->where('institute_id', $instituteId)->orderBy('id')->get();
        $staffIds = User::query()->where('institute_id', $instituteId)->orderBy('id')->pluck('id')->all();
        $students = Student::query()->where('institute_id', $instituteId)->orderBy('id')->get();

        if ($courses->isEmpty()) {
            $this->command?->error('No courses found. Seed courses/batches first.');

            return;
        }

        $interests = $courses->pluck('name')->all();
        $interests = array_merge($interests, [
            'Web Development', 'Digital Marketing', 'Graphic Design', 'Freelancing',
            'Computer Office Application', 'Electrical House Wiring',
        ]);

        $created = 0;
        $converted = 0;
        $followUpCount = 0;
        $messageCount = 0;
        $studentCursor = 0;

        foreach ($this->names as $index => $name) {
            $phone = '017' . str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT);
            $status = $this->statuses[$index] ?? 'new';
            $source = $this->sources[$index] ?? 'website';

            $course = $courses[$index % $courses->count()];
            $courseBatches = $batches->where('course_id', $course->id)->values();
            $batch = $courseBatches->isNotEmpty() ? $courseBatches[$index % $courseBatches->count()] : null;

            $student = null;
            if ($status === 'converted') {
                $student = $students->get($studentCursor + 5) ?? $students->get($studentCursor);
                $studentCursor++;

                if ($student) {
                    $course = $student->course ?: $course;
                    $batch = $student->batch ?: $batch;
                }
            }

            $createdAt = now()->copy()->subDays((int) floor(88 * (count($this->names) - 1 - $index) / (count($this->names) - 1)));

            $convertedCandidate = $createdAt->copy()->addDays(3);
            $convertedAt = $status === 'converted'
                ? ($convertedCandidate->gt(now()) ? now()->copy() : $convertedCandidate)
                : null;

            $lastContactCandidate = $createdAt->copy()->addDay();
            $lastContactedAt = $status === 'new'
                ? null
                : ($lastContactCandidate->gt(now()) ? now()->copy() : $lastContactCandidate);

            $interest = $interests[$index % count($interests)];

            $lead = Lead::firstOrCreate(
                ['institute_id' => $instituteId, 'phone' => $phone],
                [
                    'project_id' => $course->project_id ?? null,
                    'course_id' => $course->id,
                    'batch_id' => $batch?->id,
                    'assigned_to' => $staffIds ? $staffIds[$index % count($staffIds)] : null,
                    'converted_student_id' => $student?->id,
                    'name' => $name,
                    'email' => $index % 3 === 0 ? strtolower(str_replace([' ', '.'], ['', ''], $name)) . '@example.com' : null,
                    'address' => $index % 4 === 0 ? 'Mirpur, Dhaka' : ($index % 4 === 1 ? 'Uttara, Dhaka' : null),
                    'source' => $source,
                    'status' => $status,
                    'interest' => $interest,
                    'notes' => 'Asked about the ' . $interest . ' programme, class timing and fee.',
                    'lost_reason' => $status === 'lost' ? 'Admission closed — joined another institute.' : null,
                    'last_contacted_at' => $lastContactedAt,
                    'next_follow_up_at' => null,
                    'converted_at' => $convertedAt,
                ]
            );

            if (! $lead->wasRecentlyCreated) {
                continue;
            }

            $lead->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();

            $created++;
            if ($status === 'converted') {
                $converted++;
            }

            // Follow-ups: pending reminders plus a little history.
            if (in_array($status, [Lead::STATUS_CONVERTED, Lead::STATUS_LOST], true)) {
                LeadFollowUp::create([
                    'institute_id' => $instituteId,
                    'lead_id' => $lead->id,
                    'user_id' => $lead->assigned_to,
                    'type' => $status === 'converted' ? 'meeting' : 'call',
                    'status' => $status === 'converted' ? LeadFollowUp::STATUS_DONE : LeadFollowUp::STATUS_CANCELLED,
                    'scheduled_at' => $createdAt->copy()->addDays(2),
                    'completed_at' => $status === 'converted' ? $convertedAt : null,
                    'notes' => $status === 'converted' ? 'Admission meeting completed.' : 'Lead stopped responding.',
                ]);
                $followUpCount++;
            } else {
                $types = ['call', 'whatsapp', 'meeting', 'sms'];
                $pendingAt = match ($index % 5) {
                    0 => now()->copy()->subDays(2)->setTime(10, 0),
                    1 => now()->copy()->setTime(10, 30),
                    2 => now()->copy()->setTime(15, 30),
                    3 => now()->copy()->addDays(2)->setTime(11, 0),
                    default => now()->copy()->addDays(5)->setTime(16, 0),
                };

                LeadFollowUp::create([
                    'institute_id' => $instituteId,
                    'lead_id' => $lead->id,
                    'user_id' => $lead->assigned_to,
                    'type' => $types[$index % count($types)],
                    'status' => LeadFollowUp::STATUS_PENDING,
                    'scheduled_at' => $pendingAt,
                    'notes' => 'Discuss course details, fee and next batch start date.',
                ]);
                $followUpCount++;

                if ($index % 2 === 0) {
                    LeadFollowUp::create([
                        'institute_id' => $instituteId,
                        'lead_id' => $lead->id,
                        'user_id' => $lead->assigned_to,
                        'type' => 'call',
                        'status' => LeadFollowUp::STATUS_DONE,
                        'scheduled_at' => now()->copy()->subDays(7)->setTime(11, 0),
                        'completed_at' => now()->copy()->subDays(7)->setTime(11, 15),
                        'notes' => 'Intro call completed. Sent course brochure on WhatsApp.',
                    ]);
                    $followUpCount++;
                }

                $lead->syncNextFollowUp();
            }

            // Message history.
            $captureBody = 'Lead captured from ' . $lead->sourceLabel() . ' — interested in ' . $interest . '.';
            LeadMessage::create([
                'institute_id' => $instituteId,
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_to,
                'channel' => LeadMessage::CHANNEL_NOTE,
                'direction' => 'inbound',
                'status' => 'logged',
                'body' => $captureBody,
                'sent_at' => $createdAt,
            ]);
            $messageCount++;

            if ($status !== 'new' && $index % 2 === 0) {
                LeadMessage::create([
                    'institute_id' => $instituteId,
                    'lead_id' => $lead->id,
                    'user_id' => $lead->assigned_to,
                    'channel' => LeadMessage::CHANNEL_WHATSAPP,
                    'direction' => 'outbound',
                    'status' => 'sent',
                    'body' => 'Hello ' . explode(' ', $name)[0] . ', thank you for your interest in our ' . $interest
                        . ' programme. Our counsellor will call you shortly with the batch schedule and fee details.',
                    'sent_at' => $createdAt->copy()->addHours(2),
                ]);
                $messageCount++;
            }

            if ($status === 'converted' && $student) {
                LeadMessage::create([
                    'institute_id' => $instituteId,
                    'lead_id' => $lead->id,
                    'user_id' => $lead->assigned_to,
                    'channel' => LeadMessage::CHANNEL_NOTE,
                    'direction' => 'outbound',
                    'status' => 'logged',
                    'body' => 'Converted to student ' . $student->reference_no . ' — ' . $student->name_en . '.',
                    'sent_at' => $convertedAt,
                ]);
                $messageCount++;
            }
        }

        $this->command?->info(sprintf(
            'Dummy CRM ready: %d leads (%d converted), %d follow-ups, %d timeline messages.',
            $created,
            $converted,
            $followUpCount,
            $messageCount
        ));
    }
}
