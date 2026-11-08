<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_dashboard_renders_with_dated_batches()
    {
        $institute = Institute::create([
            'name' => 'Dashboard Institute',
            'code' => 'DASH01',
            'mobile' => '01755555555',
            'email' => 'dash@test.com',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin D',
            'email' => 'admin.dash@test.com',
            'password' => bcrypt('password'),
            'institute_id' => $institute->id,
            'role' => 'admin',
        ]);

        $project = Project::create([
            'institute_id' => $institute->id,
            'name' => 'Dashboard Project',
            'status' => 'active',
        ]);

        $course = Course::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'name' => 'Web Dev',
            'fee' => 5000,
            'seat_capacity' => 30,
        ]);

        // Regression: dashboard must render batches with start/end dates
        // (start_date/end_date are date-cast on the model).
        Batch::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'name' => 'Batch A',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'seat_capacity' => 20,
        ]);

        $this->actingAs($user)
            ->get("/projects/{$project->id}/dashboard")
            ->assertOk()
            ->assertSee('Batch A')
            ->assertSee('01 Jul - 31 Dec 2026');
    }
}
