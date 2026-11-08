<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_page_shows_institutes()
    {
        Institute::create(['name' => 'Test Inst', 'code' => 'TST01', 'mobile' => '01711111111', 'email' => 't@t.com', 'status' => 'active']);
        $this->get('/apply')->assertSee('Test Inst');
    }

    public function test_student_can_apply()
    {
        $inst = Institute::create(['name' => 'Test', 'code' => 'TST', 'mobile' => '01711111111', 'email' => 't@t.com', 'status' => 'active']);
        $project = Project::create(['institute_id' => $inst->id, 'name' => 'STP Project', 'status' => 'active']);
        $course = Course::create(['institute_id' => $inst->id, 'project_id' => $project->id, 'name' => 'Web Dev', 'fee' => 0, 'seat_capacity' => 30]);
        $batch = Batch::create(['institute_id' => $inst->id, 'project_id' => $project->id, 'course_id' => $course->id, 'name' => 'Batch 1', 'seat_capacity' => 20]);

        $response = $this->post('/apply/store', [
            'institute_id' => $inst->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'name_en' => 'Student One',
            'name_bn' => 'স্টুডেন্ট ওয়ান',
            'father_name_en' => 'Father One',
            'mother_name_en' => 'Mother One',
            'contact' => '01712345678',
            'dob' => '2000-01-01',
            'gender' => 'Male',
            'id_type' => 'NID',
            'id_no' => '1234567890',
            'guardian_contact' => '01712345679',
            'guardian_relation' => 'Father',
        ]);

        $response->assertRedirectContains('/apply/success/');
        $this->assertDatabaseHas('students', ['name_en' => 'Student One', 'status' => 'applied']);
    }

    public function test_admin_can_approve_student()
    {
        $inst = Institute::create(['name' => 'Test', 'code' => 'TST', 'mobile' => '01711111111', 'email' => 't@t.com', 'status' => 'active']);
        $user = User::create(['name' => 'Admin', 'email' => 'a@t.com', 'password' => bcrypt('p'), 'institute_id' => $inst->id, 'role' => 'admin']);
        $course = Course::create(['institute_id' => $inst->id, 'name' => 'Web', 'fee' => 0, 'seat_capacity' => 30]);
        $batch = Batch::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'name' => 'B1', 'seat_capacity' => 20]);
        $student = Student::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'batch_id' => $batch->id, 'name_en' => 'S1', 'father_name_en' => 'F', 'mother_name_en' => 'M', 'contact' => '01712345678', 'dob' => '2000-01-01', 'gender' => 'Male', 'id_type' => 'NID', 'id_no' => '123', 'guardian_contact' => '01712345679', 'guardian_relation' => 'F', 'reference_no' => 'STP-TEST123', 'status' => 'applied']);

        $this->actingAs($user)->post("/applications/{$student->id}/approve")->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'admitted']);
    }

    public function test_admin_can_change_student_batch()
    {
        $inst = Institute::create(['name' => 'Test', 'code' => 'TST', 'mobile' => '01711111111', 'email' => 't@t.com', 'status' => 'active']);
        $user = User::create(['name' => 'Admin', 'email' => 'a@t.com', 'password' => bcrypt('p'), 'institute_id' => $inst->id, 'role' => 'admin']);
        $course = Course::create(['institute_id' => $inst->id, 'name' => 'Web', 'fee' => 0, 'seat_capacity' => 30]);
        $batch1 = Batch::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'name' => 'B1', 'seat_capacity' => 20]);
        $batch2 = Batch::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'name' => 'B2', 'seat_capacity' => 20]);
        $student = Student::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'batch_id' => $batch1->id, 'name_en' => 'S1', 'father_name_en' => 'F', 'mother_name_en' => 'M', 'contact' => '01712345678', 'dob' => '2000-01-01', 'gender' => 'Male', 'id_type' => 'NID', 'id_no' => '123', 'guardian_contact' => '01712345679', 'guardian_relation' => 'F', 'reference_no' => 'STP-TEST456', 'status' => 'admitted']);

        $this->actingAs($user)->post("/applications/{$student->id}/change-batch", ['batch_id' => $batch2->id])->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'batch_id' => $batch2->id]);
    }

    public function test_admin_can_remove_student_from_batch()
    {
        $inst = Institute::create(['name' => 'Test', 'code' => 'TST', 'mobile' => '01711111111', 'email' => 't@t.com', 'status' => 'active']);
        $user = User::create(['name' => 'Admin', 'email' => 'a@t.com', 'password' => bcrypt('p'), 'institute_id' => $inst->id, 'role' => 'admin']);
        $course = Course::create(['institute_id' => $inst->id, 'name' => 'Web', 'fee' => 0, 'seat_capacity' => 30]);
        $batch = Batch::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'name' => 'B1', 'seat_capacity' => 20]);
        $student = Student::create(['institute_id' => $inst->id, 'course_id' => $course->id, 'batch_id' => $batch->id, 'name_en' => 'S1', 'father_name_en' => 'F', 'mother_name_en' => 'M', 'contact' => '01712345678', 'dob' => '2000-01-01', 'gender' => 'Male', 'id_type' => 'NID', 'id_no' => '123', 'guardian_contact' => '01712345679', 'guardian_relation' => 'F', 'reference_no' => 'STP-TEST789', 'status' => 'admitted']);

        $this->actingAs($user)->post("/applications/{$student->id}/remove-batch")->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'batch_id' => null, 'status' => 'applied']);
    }
}
