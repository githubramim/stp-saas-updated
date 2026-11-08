<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createData(): array
    {
        $institute = Institute::create([
            'name' => 'Test Institute', 'code' => 'STPTEST',
            'mobile' => '01712345678', 'email' => 'inst@test.com', 'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'institute_id' => $institute->id, 'role' => 'admin',
        ]);

        $course = Course::create([
            'institute_id' => $institute->id, 'name' => 'Web Dev',
            'fee' => 10000, 'seat_capacity' => 30,
        ]);

        return [$institute, $user, $course];
    }

    public function test_batch_list_page_is_displayed()
    {
        [, $user] = $this->createData();
        $this->actingAs($user)->get('/batches')->assertOk();
    }

    public function test_batch_can_be_created()
    {
        [, $user, $course] = $this->createData();

        $response = $this->actingAs($user)->post('/batches', [
            'course_id' => $course->id,
            'name' => 'Batch 01',
            'name_bn' => 'ব্যাচ ০১',
            'shift' => 'Morning',
            'seat_capacity' => 20,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
        ]);

        $response->assertRedirect('/batches');
        $this->assertDatabaseHas('batches', [
            'name' => 'Batch 01', 'shift' => 'Morning', 'seat_capacity' => 20,
        ]);
    }

    public function test_batch_requires_name()
    {
        [, $user, $course] = $this->createData();

        $response = $this->actingAs($user)->post('/batches', [
            'course_id' => $course->id,
            'name' => '',
            'seat_capacity' => 10,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_batch_belongs_to_institute()
    {
        [, $user] = $this->createData();

        $other = Institute::create([
            'name' => 'Other', 'code' => 'OTHER',
            'mobile' => '01712345679', 'email' => 'o@t.com',
        ]);
        $otherCourse = Course::create([
            'institute_id' => $other->id, 'name' => 'Other', 'fee' => 0, 'seat_capacity' => 10,
        ]);
        $batch = Batch::create([
            'institute_id' => $other->id, 'course_id' => $otherCourse->id,
            'name' => 'Other Batch', 'seat_capacity' => 10,
        ]);

        $this->actingAs($user)->get("/batches/{$batch->id}/edit")->assertForbidden();
    }

    public function test_batch_can_be_updated()
    {
        [, $user, $course] = $this->createData();

        $batch = Batch::create([
            'institute_id' => $user->institute_id, 'course_id' => $course->id,
            'name' => 'Old Batch', 'seat_capacity' => 10,
        ]);

        $this->actingAs($user)->patch("/batches/{$batch->id}", [
            'course_id' => $course->id,
            'name' => 'Updated Batch',
            'seat_capacity' => 25,
        ])->assertRedirect('/batches');

        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'name' => 'Updated Batch', 'seat_capacity' => 25]);
    }

    public function test_batch_can_be_deleted()
    {
        [, $user, $course] = $this->createData();

        $batch = Batch::create([
            'institute_id' => $user->institute_id, 'course_id' => $course->id,
            'name' => 'To Delete', 'seat_capacity' => 10,
        ]);

        $this->actingAs($user)->delete("/batches/{$batch->id}")->assertRedirect('/batches');
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }
}
