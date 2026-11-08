<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function base(string $tag = 'a'): array
    {
        $institute = Institute::create([
            'name' => 'Test Institute '.$tag,
            'code' => 'STPTEST'.strtoupper($tag),
            'mobile' => '0171234567'.$tag,
            'email' => "inst{$tag}@test.com",
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin '.$tag,
            'email' => "admin{$tag}@test.com",
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

    private function makeStudent($institute, $project, $course, $batch, string $ref = 'STP-0001'): Student
    {
        return Student::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'name_en' => 'Rahim Uddin',
            'father_name_en' => 'Md Karim',
            'mother_name_en' => 'Rokeya Begum',
            'contact' => '01711111111',
            'dob' => '2000-01-15',
            'gender' => 'male',
            'id_type' => 'nid',
            'id_no' => '1990123456',
            'guardian_contact' => '01722222222',
            'guardian_relation' => 'father',
            'reference_no' => $ref,
            'status' => 'admitted',
        ]);
    }

    private function makeSession($institute, $project, $course, $batch, string $date = '2026-10-03'): AttendanceSession
    {
        return AttendanceSession::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'title' => 'Day 1 Class',
            'session_date' => $date,
            'start_time' => '09:00',
            'late_after_minutes' => 10,
            'status' => AttendanceSession::STATUS_OPEN,
            'qr_token' => 'test-token-1',
            'qr_expires_at' => now()->addHours(2),
            'taken_by' => null,
        ]);
    }

    public function test_attendance_pages_require_authentication()
    {
        $this->get('/attendance')->assertRedirect('/login');
        $this->get('/attendance/create')->assertRedirect('/login');
        $this->get('/attendance/monthly-report')->assertRedirect('/login');
    }

    public function test_attendance_index_and_create_pages_render()
    {
        [, $user] = $this->base();

        $this->actingAs($user)->get('/attendance')->assertOk();
        $this->actingAs($user)->get('/attendance/create')->assertOk();
    }

    public function test_session_can_be_created()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();

        $response = $this->actingAs($user)->post('/attendance', [
            'batch_id' => $batch->id,
            'title' => 'Day 1 Class',
            'session_date' => '2026-10-03',
            'start_time' => '09:00',
            'late_after_minutes' => 10,
        ]);

        $session = AttendanceSession::first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('attendance.show', $session));

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'institute_id' => $institute->id,
            'batch_id' => $batch->id,
            'title' => 'Day 1 Class',
            'status' => AttendanceSession::STATUS_OPEN,
        ]);
    }

    public function test_session_creation_requires_title_and_date()
    {
        [, $user, , , $batch] = $this->base();

        $this->actingAs($user)
            ->post('/attendance', ['batch_id' => $batch->id])
            ->assertSessionHasErrors(['title', 'session_date']);
    }

    public function test_trainer_can_mark_attendance_manually()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $student = $this->makeStudent($institute, $project, $course, $batch);
        $session = $this->makeSession($institute, $project, $course, $batch);

        $response = $this->actingAs($user)->post("/attendance/{$session->id}/mark", [
            'attendance' => [$student->id => Attendance::STATUS_PRESENT],
        ]);

        $response->assertRedirect(route('attendance.show', $session));

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => Attendance::STATUS_PRESENT,
            'method' => Attendance::METHOD_MANUAL,
        ]);
    }

    public function test_closing_session_marks_unmarked_students_absent()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $marked = $this->makeStudent($institute, $project, $course, $batch, 'STP-0001');
        $unmarked = $this->makeStudent($institute, $project, $course, $batch, 'STP-0002');
        $session = $this->makeSession($institute, $project, $course, $batch);

        $this->actingAs($user)->post("/attendance/{$session->id}/mark", [
            'attendance' => [$marked->id => Attendance::STATUS_PRESENT],
        ]);

        $this->actingAs($user)->post("/attendance/{$session->id}/close")->assertRedirect(route('attendance.show', $session));

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $unmarked->id,
            'status' => Attendance::STATUS_ABSENT,
        ]);

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'status' => AttendanceSession::STATUS_CLOSED,
        ]);
    }

    public function test_student_can_mark_via_qr_scan_with_reference_and_dob()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $student = $this->makeStudent($institute, $project, $course, $batch);
        $session = $this->makeSession($institute, $project, $course, $batch);

        // No fixed start time => the scan is recorded as present regardless of when it happens.
        $session->update(['start_time' => null]);

        $this->get('/attendance/scan/test-token-1')->assertOk();

        $this->post('/attendance/scan/test-token-1', [
            'reference_no' => 'STP-0001',
            'dob' => '2000-01-15',
        ])->assertRedirect(route('attendance.scan', 'test-token-1'));

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => Attendance::STATUS_PRESENT,
            'method' => Attendance::METHOD_QR,
        ]);
    }

    public function test_scan_rejects_wrong_date_of_birth()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $this->makeStudent($institute, $project, $course, $batch);
        $this->makeSession($institute, $project, $course, $batch);

        $this->post('/attendance/scan/test-token-1', [
            'reference_no' => 'STP-0001',
            'dob' => '1999-12-31',
        ])->assertSessionHasErrors('reference_no');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_scan_ignores_duplicate_submission()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $student = $this->makeStudent($institute, $project, $course, $batch);
        $this->makeSession($institute, $project, $course, $batch);

        $payload = ['reference_no' => 'STP-0001', 'dob' => '2000-01-15'];

        $this->post('/attendance/scan/test-token-1', $payload);
        $this->post('/attendance/scan/test-token-1', $payload);

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_scan_rejects_invalid_token()
    {
        $this->get('/attendance/scan/unknown-token')->assertNotFound();
    }

    public function test_monthly_and_project_reports_render_and_export()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $student = $this->makeStudent($institute, $project, $course, $batch);
        $session = $this->makeSession($institute, $project, $course, $batch);

        $this->actingAs($user)->post("/attendance/{$session->id}/mark", [
            'attendance' => [$student->id => Attendance::STATUS_PRESENT],
        ]);

        $this->actingAs($user)->get('/attendance/monthly-report?month=2026-10')->assertOk();
        $this->actingAs($user)->get('/attendance/project-report?month=2026-10')->assertOk();

        $csv = $this->actingAs($user)->get('/attendance/monthly-report.csv?month=2026-10');
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_attendance_of_other_institute_is_forbidden()
    {
        [, $user] = $this->base();

        [$otherInstitute, , $otherProject, $otherCourse, $otherBatch] = $this->base('b');
        $session = $this->makeSession($otherInstitute, $otherProject, $otherCourse, $otherBatch);

        $this->actingAs($user)->get("/attendance/{$session->id}")->assertForbidden();
        $this->actingAs($user)->post("/attendance/{$session->id}/close")->assertForbidden();
    }

    public function test_regenerating_qr_changes_token()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $session = $this->makeSession($institute, $project, $course, $batch);

        $this->actingAs($user)->post("/attendance/{$session->id}/qr/regenerate")->assertRedirect();

        $this->assertNotSame('test-token-1', $session->fresh()->qr_token);
    }

    public function test_session_can_be_deleted()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $session = $this->makeSession($institute, $project, $course, $batch);

        $this->actingAs($user)->delete("/attendance/{$session->id}")->assertRedirect('/attendance');

        $this->assertDatabaseMissing('attendance_sessions', ['id' => $session->id]);
    }
}
