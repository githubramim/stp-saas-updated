<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\GradeScale;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\GradeScaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamResultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GradeScaleSeeder::class);
    }

    private function base(string $tag = 'a'): array
    {
        $institute = Institute::create([
            'name' => 'Test Institute '.$tag,
            'code' => 'EXAM'.$tag,
            'mobile' => '0179999'.substr($tag, 0, 2),
            'email' => "exam{$tag}@test.com",
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Admin '.$tag,
            'email' => "teacher{$tag}@test.com",
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
            'name' => 'Course '.$tag,
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

    private function makeStudent($institute, $project, $course, $batch, string $ref): Student
    {
        return Student::create([
            'institute_id' => $institute->id,
            'project_id' => $project->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'name_en' => 'Student '.$ref,
            'father_name_en' => 'Father',
            'mother_name_en' => 'Mother',
            'contact' => '01711111111',
            'dob' => '2000-01-15',
            'gender' => 'male',
            'id_type' => 'nid',
            'id_no' => '1990'.$ref,
            'guardian_contact' => '01722222222',
            'guardian_relation' => 'father',
            'reference_no' => $ref,
            'status' => 'admitted',
        ]);
    }

    private function makeExam($institute, $user, $project, $course, $batch): Exam
    {
        $response = $this->actingAs($user)->post('/exams', [
            'batch_id' => $batch->id,
            'title' => 'Mid Term',
            'exam_date' => '2026-10-03',
            'duration_minutes' => 90,
            'questions' => [
                [
                    'question' => 'What is 2 + 2?',
                    'question_type' => 'mcq',
                    'option_a' => '3',
                    'option_b' => '4',
                    'option_c' => '5',
                    'option_d' => '6',
                    'correct_option' => 'b',
                    'marks' => 10,
                    'negative_marks' => 1,
                ],
                [
                    'question' => 'Explain gravity.',
                    'question_type' => 'written',
                    'marks' => 20,
                ],
            ],
        ]);

        $exam = Exam::first();
        $this->assertNotNull($exam, 'Exam was not created.');
        $response->assertRedirect(route('exams.show', $exam));

        return $exam;
    }

    public function test_exam_pages_require_authentication()
    {
        $this->get('/exams')->assertRedirect('/login');
        $this->get('/exams/create')->assertRedirect('/login');
    }

    public function test_exam_index_and_create_pages_render()
    {
        [, $user] = $this->base();

        $this->actingAs($user)->get('/exams')->assertOk();
        $this->actingAs($user)->get('/exams/create')->assertOk();
    }

    public function test_exam_is_created_with_questions_and_derived_total()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch)->fresh();

        $this->assertDatabaseCount('exam_questions', 2);
        $this->assertSame(30.0, (float) $exam->total_marks);
        $this->assertSame(12.0, (float) $exam->pass_marks);
        $this->assertSame(Exam::STATUS_DRAFT, $exam->status);
        $this->assertSame('mixed', $exam->exam_type);
    }

    public function test_exam_requires_at_least_one_question()
    {
        [, $user, , , $batch] = $this->base();

        $this->actingAs($user)->post('/exams', [
            'batch_id' => $batch->id,
            'title' => 'Empty Exam',
            'exam_date' => '2026-10-03',
            'questions' => [],
        ])->assertSessionHasErrors('questions');

        $this->assertDatabaseCount('exams', 0);
    }

    public function test_mcq_answers_are_auto_graded_with_negative_marking()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $good = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');
        $wrong = $this->makeStudent($institute, $project, $course, $batch, 'EX-0002');

        $mcq = $exam->questions()->where('question_type', 'mcq')->first();

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'answers' => [
                $good->id => [$mcq->id => 'b'],
                $wrong->id => [$mcq->id => 'a'],
            ],
            'manual' => [
                $good->id => 20,
                $wrong->id => 20,
            ],
            'absent' => [
                $good->id => '',
                $wrong->id => '',
            ],
        ])->assertRedirect(route('exams.results', $exam));

        // correct MCQ (10) + manual 20 = 30
        $this->assertSame(30.0, (float) ExamResult::where('student_id', $good->id)->value('obtained_marks'));

        // wrong MCQ (-1) + manual 20 = 19
        $this->assertSame(19.0, (float) ExamResult::where('student_id', $wrong->id)->value('obtained_marks'));

        $this->assertDatabaseHas('exam_answers', [
            'exam_result_id' => ExamResult::where('student_id', $good->id)->value('id'),
            'exam_question_id' => $mcq->id,
            'given_answer' => 'b',
            'is_correct' => true,
            'marks_awarded' => 10,
        ]);
    }

    public function test_merit_list_is_ordered_and_positions_are_shared()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $first = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');
        $second = $this->makeStudent($institute, $project, $course, $batch, 'EX-0002');
        $third = $this->makeStudent($institute, $project, $course, $batch, 'EX-0003');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$first->id => 25, $second->id => 15, $third->id => 15],
            'absent' => [$first->id => '', $second->id => '', $third->id => ''],
        ])->assertRedirect();

        $this->actingAs($user)->get("/exams/{$exam->id}/results")->assertOk();

        $this->assertSame(1, ExamResult::where('student_id', $first->id)->first()->meritPosition());
        $this->assertSame(2, ExamResult::where('student_id', $second->id)->first()->meritPosition());
        $this->assertSame(2, ExamResult::where('student_id', $third->id)->first()->meritPosition());
    }

    public function test_absent_flag_overrides_marks()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$student->id => 30],
            'absent' => [$student->id => 1],
        ])->assertRedirect();

        $result = ExamResult::where('student_id', $student->id)->first();

        $this->assertTrue((bool) $result->is_absent);
        $this->assertSame(0.0, (float) $result->obtained_marks);
        $this->assertSame('F', $result->grade_letter);
        $this->assertFalse((bool) $result->is_pass);
    }

    public function test_grade_comes_from_grade_scale()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $mcq = $exam->questions()->where('question_type', 'mcq')->first();

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'answers' => [$student->id => [$mcq->id => 'b']],
            'manual' => [$student->id => 20],
            'absent' => [$student->id => ''],
        ])->assertRedirect();

        $result = ExamResult::where('student_id', $student->id)->first();

        $this->assertSame(100.0, (float) $result->percentage);
        $this->assertTrue((bool) $result->is_pass);

        $band = GradeScale::resolveFor($institute->id, 100.0);
        $this->assertSame($band->grade_letter, $result->grade_letter);
        $this->assertSame((float) $band->grade_point, (float) $result->grade_point);
    }

    public function test_manual_marks_are_capped_at_the_written_ceiling()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$student->id => 999],
            'absent' => [$student->id => ''],
        ])->assertRedirect();

        $this->assertSame(20.0, (float) ExamResult::where('student_id', $student->id)->value('obtained_marks'));
    }

    public function test_exam_can_be_published_and_reverted()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $this->actingAs($user)->post("/exams/{$exam->id}/publish")->assertRedirect();

        $this->assertSame(Exam::STATUS_PUBLISHED, $exam->fresh()->status);
        $this->assertNotNull($exam->fresh()->published_at);

        $this->actingAs($user)->post("/exams/{$exam->id}/revert")->assertRedirect();

        $this->assertSame(Exam::STATUS_DRAFT, $exam->fresh()->status);
        $this->assertNull($exam->fresh()->published_at);
    }

    public function test_exam_can_be_updated_and_deleted()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $this->actingAs($user)->get("/exams/{$exam->id}/edit")->assertOk();

        $this->actingAs($user)->put("/exams/{$exam->id}", [
            'batch_id' => $batch->id,
            'title' => 'Final Exam',
            'exam_date' => '2026-12-10',
            'questions' => [
                ['question' => 'Only one question now.', 'question_type' => 'written', 'marks' => 50],
            ],
        ])->assertRedirect();

        $exam->refresh();
        $this->assertSame('Final Exam', $exam->title);
        $this->assertSame(50.0, (float) $exam->total_marks);
        $this->assertDatabaseCount('exam_questions', 1);

        $this->actingAs($user)->delete("/exams/{$exam->id}")->assertRedirect(route('exams.index'));
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_exam_of_other_institute_is_forbidden()
    {
        [, $user] = $this->base();
        [$otherInstitute, $otherUser, $otherProject, $otherCourse, $otherBatch] = $this->base('b');
        $exam = $this->makeExam($otherInstitute, $otherUser, $otherProject, $otherCourse, $otherBatch);

        $this->actingAs($user)->get("/exams/{$exam->id}")->assertForbidden();
        $this->actingAs($user)->post("/exams/{$exam->id}/publish")->assertForbidden();
    }

    public function test_results_csv_and_report_card_render()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);

        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$student->id => 18],
            'absent' => [$student->id => ''],
        ])->assertRedirect();

        $csv = $this->actingAs($user)->get("/exams/{$exam->id}/results.csv");
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($user)->get("/exams/{$exam->id}/report-card/{$student->id}")->assertOk();
    }

    public function test_portal_hides_unpublished_results()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);
        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$student->id => 20],
            'absent' => [$student->id => ''],
        ])->assertRedirect();

        $this->get('/portal')->assertOk();

        // Still a draft: the portal must not reveal it.
        $this->post('/portal', ['reference_no' => 'EX-0001', 'dob' => '2000-01-15'])
            ->assertOk()
            ->assertSee('No results have been published yet');

        $this->actingAs($user)->post("/exams/{$exam->id}/publish");

        $this->post('/portal', ['reference_no' => 'EX-0001', 'dob' => '2000-01-15'])
            ->assertOk()
            ->assertSee('Mid Term')
            ->assertSee('Published Results');
    }

    public function test_portal_rejects_wrong_credentials()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->post('/portal', ['reference_no' => 'EX-0001', 'dob' => '1980-05-05'])
            ->assertSessionHasErrors('reference_no');

        $this->post('/portal', ['reference_no' => 'NOPE', 'dob' => '2000-01-15'])
            ->assertSessionHasErrors('reference_no');

        $this->post('/portal', [])->assertSessionHasErrors(['reference_no', 'dob']);
    }

    public function test_cgpa_counts_only_published_exams()
    {
        [$institute, $user, $project, $course, $batch] = $this->base();
        $exam = $this->makeExam($institute, $user, $project, $course, $batch);
        $student = $this->makeStudent($institute, $project, $course, $batch, 'EX-0001');

        $this->actingAs($user)->post("/exams/{$exam->id}/marks", [
            'manual' => [$student->id => 30],
            'absent' => [$student->id => ''],
        ]);

        $this->assertSame(0.0, ExamResult::cgpaFor($student->id, $institute->id));

        $this->actingAs($user)->post("/exams/{$exam->id}/publish");

        $this->assertGreaterThan(0.0, ExamResult::cgpaFor($student->id, $institute->id));
    }

    public function test_exam_question_grading_helpers()
    {
        [, , , , $batch] = $this->base();

        $mcq = new ExamQuestion([
            'question' => 'Q',
            'question_type' => ExamQuestion::TYPE_MCQ,
            'option_a' => 'A',
            'option_b' => 'B',
            'correct_option' => 'b',
            'marks' => 5,
            'negative_marks' => 1,
        ]);

        $this->assertTrue($mcq->isMcq());
        $this->assertTrue($mcq->hasNegativeMarking());
        $this->assertTrue($mcq->isCorrect('B'));
        $this->assertFalse($mcq->isCorrect('a'));
        $this->assertNull($mcq->isCorrect(null));
        $this->assertSame(5.0, $mcq->awardFor('b'));
        $this->assertSame(-1.0, $mcq->awardFor('a'));
        $this->assertSame(0.0, $mcq->awardFor(null));
        $this->assertSame(['a' => 'A', 'b' => 'B'], $mcq->options());
    }
}
