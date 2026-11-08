<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function base(string $tag = 'a'): array
    {
        $institute = Institute::create([
            'name' => 'Test Institute '.$tag,
            'code' => 'SCHED'.$tag,
            'mobile' => '0173333'.$tag,
            'email' => "sched{$tag}@test.com",
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin '.$tag,
            'email' => "admin.sched{$tag}@test.com",
            'password' => bcrypt('password'),
            'institute_id' => $institute->id,
            'role' => 'admin',
        ]);

        $project = Project::create([
            'institute_id' => $institute->id,
            'name' => 'STP Project '.$tag,
            'status' => 'active',
        ]);

        $course = Course::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'name' => 'Web Development '.$tag,
        ]);

        $batch = Batch::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'name' => 'Batch '.$tag,
            'seat_capacity' => 30,
        ]);

        return [$institute, $user, $project, $course, $batch];
    }

    private function makeTrainer($institute, string $tag = 't'): User
    {
        return User::create([
            'name' => 'Trainer '.$tag,
            'email' => "trainer.sched{$tag}@test.com",
            'password' => bcrypt('password'),
            'institute_id' => $institute->id,
            'role' => 'staff',
        ]);
    }

    private function makeSchedule($institute, $project, $batch, array $overrides = []): Schedule
    {
        return Schedule::create(array_merge([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'batch_id' => $batch->id,
            'day_of_week' => 0, // Saturday
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Room 101',
            'subject' => 'PHP Fundamentals',
            'status' => 'active',
        ], $overrides));
    }

    private function payload($batch, array $overrides = []): array
    {
        return array_merge([
            'batch_id' => $batch->id,
            'day_of_week' => 0,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Room 101',
            'trainer_id' => '',
            'subject' => 'PHP Fundamentals',
            'remarks' => '',
        ], $overrides);
    }

    public function test_schedule_pages_require_authentication()
    {
        $this->get('/schedules')->assertRedirect('/login');
        $this->get('/schedules/create')->assertRedirect('/login');
        $this->get('/schedules/print')->assertRedirect('/login');
    }

    public function test_index_and_create_pages_render()
    {
        [, $user] = $this->base();

        $this->actingAs($user)->get('/schedules')->assertOk();
        $this->actingAs($user)->get('/schedules/create')->assertOk();
    }

    public function test_schedule_can_be_created()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $trainer = $this->makeTrainer($institute);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($batch, ['trainer_id' => $trainer->id]))
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'batch_id' => $batch->id,
            'day_of_week' => 0,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Room 101',
            'trainer_id' => $trainer->id,
        ]);
    }

    public function test_creation_requires_day_and_time()
    {
        [, $user, , , $batch] = $this->base();

        $this->actingAs($user)
            ->post('/schedules', ['batch_id' => $batch->id])
            ->assertSessionHasErrors(['day_of_week', 'start_time', 'end_time']);

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_end_time_must_be_after_start_time()
    {
        [, $user, , , $batch] = $this->base();

        $this->actingAs($user)
            ->post('/schedules', $this->payload($batch, ['start_time' => '12:00', 'end_time' => '10:00']))
            ->assertSessionHasErrors('end_time');
    }

    public function test_same_room_same_time_conflicts()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $otherBatch = Batch::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => Batch::find($batch->id)->course_id,
            'name' => 'Batch B2',
            'seat_capacity' => 30,
        ]);

        $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($otherBatch))
            ->assertSessionHasErrors('day_of_week');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_same_trainer_same_time_conflicts()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $trainer = $this->makeTrainer($institute);

        $this->makeSchedule($institute, $project, $batch, ['trainer_id' => $trainer->id]);

        $otherBatch = Batch::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => Batch::find($batch->id)->course_id,
            'name' => 'Batch C2',
            'seat_capacity' => 30,
        ]);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($otherBatch, [
                'room' => 'Room 202',
                'trainer_id' => $trainer->id,
            ]))
            ->assertSessionHasErrors('day_of_week');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_same_batch_same_time_conflicts()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($batch, ['room' => 'Room 202']))
            ->assertSessionHasErrors('day_of_week');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_same_room_on_different_day_is_allowed()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($batch, ['day_of_week' => 1]))
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_same_batch_at_different_time_is_allowed()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->post('/schedules', $this->payload($batch, ['start_time' => '12:00', 'end_time' => '14:00']))
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_two_batches_same_time_with_no_shared_resource_are_allowed()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $otherBatch = Batch::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => Batch::find($batch->id)->course_id,
            'name' => 'Batch D2',
            'seat_capacity' => 30,
        ]);

        $this->makeSchedule($institute, $project, $batch, ['trainer_id' => $this->makeTrainer($institute, 't1')->id]);
        $trainer2 = $this->makeTrainer($institute, 't2');

        $this->actingAs($user)
            ->post('/schedules', $this->payload($otherBatch, [
                'room' => 'Room 202',
                'trainer_id' => $trainer2->id,
            ]))
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_schedule_can_be_updated_without_false_conflict()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $schedule = $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->put("/schedules/{$schedule->id}", $this->payload($batch, ['subject' => 'Laravel Basics']))
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'subject' => 'Laravel Basics',
        ]);
    }

    public function test_schedule_can_be_deleted()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $schedule = $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)
            ->delete("/schedules/{$schedule->id}")
            ->assertRedirect(route('schedules.index'));

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_schedule_of_other_institute_is_forbidden()
    {
        [, $user] = $this->base();
        [$otherInstitute, $otherUser, $otherProject, , $otherBatch] = $this->base('b');
        $schedule = $this->makeSchedule($otherInstitute, $otherProject, $otherBatch);

        $this->actingAs($user)->get("/schedules/{$schedule->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/schedules/{$schedule->id}", $this->payload($otherBatch))->assertForbidden();

        $otherSchedule = $this->makeSchedule($otherInstitute, $otherProject, $otherBatch, ['room' => 'X']);
        $this->actingAs($otherUser)->delete("/schedules/{$otherSchedule->id}")->assertRedirect(route('schedules.index'));
    }

    public function test_print_page_renders()
    {
        [$institute, $user, $project, , $batch] = $this->base();
        $this->makeSchedule($institute, $project, $batch);

        $this->actingAs($user)->get('/schedules/print')->assertOk()->assertSee('Class Routine');
    }
}