<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createInstituteAndUser(): array
    {
        $institute = Institute::create([
            'name' => 'Test Institute',
            'code' => 'STPTEST',
            'mobile' => '01712345678',
            'email' => 'inst@test.com',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'institute_id' => $institute->id,
            'role' => 'admin',
        ]);

        return [$institute, $user];
    }

    public function test_course_list_page_is_displayed()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $response = $this->actingAs($user)->get('/courses');
        $response->assertOk();
    }

    public function test_course_can_be_created()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $response = $this->actingAs($user)->post('/courses', [
            'name' => 'Web Development',
            'name_bn' => 'ওয়েব ডেভেলপমেন্ট',
            'duration' => '6 months',
            'fee' => 25000,
            'seat_capacity' => 30,
            'description' => 'Learn web development',
        ]);

        $response->assertRedirect('/courses');

        $this->assertDatabaseHas('courses', [
            'institute_id' => $institute->id,
            'name' => 'Web Development',
            'fee' => 25000,
            'seat_capacity' => 30,
        ]);
    }

    public function test_course_requires_name()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $response = $this->actingAs($user)->post('/courses', [
            'name' => '',
            'fee' => 1000,
            'seat_capacity' => 10,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_course_belongs_to_institute()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $otherInstitute = Institute::create([
            'name' => 'Other Institute',
            'code' => 'OTHER',
            'mobile' => '01712345679',
            'email' => 'other@test.com',
        ]);

        $course = Course::create([
            'institute_id' => $otherInstitute->id,
            'name' => 'Other Course',
            'fee' => 1000,
            'seat_capacity' => 10,
        ]);

        $response = $this->actingAs($user)->get("/courses/{$course->id}/edit");
        $response->assertForbidden();
    }

    public function test_course_can_be_updated()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $course = Course::create([
            'institute_id' => $institute->id,
            'name' => 'Old Name',
            'fee' => 1000,
            'seat_capacity' => 10,
        ]);

        $response = $this->actingAs($user)->patch("/courses/{$course->id}", [
            'name' => 'Updated Name',
            'fee' => 2000,
            'seat_capacity' => 20,
        ]);

        $response->assertRedirect('/courses');
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Updated Name']);
    }

    public function test_course_can_be_deleted()
    {
        [$institute, $user] = $this->createInstituteAndUser();

        $course = Course::create([
            'institute_id' => $institute->id,
            'name' => 'To Delete',
            'fee' => 1000,
            'seat_capacity' => 10,
        ]);

        $response = $this->actingAs($user)->delete("/courses/{$course->id}");
        $response->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
