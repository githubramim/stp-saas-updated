<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddDemoTrainersSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::with(['courses.batches', 'institute'])->get();

        if ($projects->isEmpty()) {
            $this->command->info("No projects found in database.");
            return;
        }

        $passwordHash = Hash::make('password123');
        $createdCount = 0;

        foreach ($projects as $project) {
            $instituteId = $project->institute_id ?? 1;

            foreach ($project->courses as $course) {
                foreach ($course->batches as $batchIndex => $batch) {
                    $slug = Str::slug($project->code . '-' . $course->course_code . '-' . $batch->name);

                    // Trainer 1: Lead Demo Trainer
                    $trainer1Email = "trainer.lead.{$batch->id}@demo.com";
                    $trainer1 = User::firstOrCreate(
                        ['email' => $trainer1Email],
                        [
                            'name' => "Lead Trainer - " . $batch->name,
                            'password' => $passwordHash,
                            'phone' => '017' . rand(10000000, 99999999),
                            'role' => 'trainer',
                            'institute_id' => $instituteId,
                        ]
                    );

                    // Trainer 2: Assistant / Co-Demo Trainer
                    $trainer2Email = "trainer.assistant.{$batch->id}@demo.com";
                    $trainer2 = User::firstOrCreate(
                        ['email' => $trainer2Email],
                        [
                            'name' => "Assistant Trainer - " . $batch->name,
                            'password' => $passwordHash,
                            'phone' => '018' . rand(10000000, 99999999),
                            'role' => 'trainer',
                            'institute_id' => $instituteId,
                        ]
                    );

                    // Assign Lead Trainer to batch trainer_id
                    $batch->update([
                        'trainer_id' => $trainer1->id,
                    ]);

                    $createdCount += 2;
                }
            }
        }

        echo "Created and assigned Demo Trainers for all batches successfully. Total created/updated: {$createdCount} trainers.\n";
    }
}
