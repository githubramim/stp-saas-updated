<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamResultController extends Controller
{
    /**
     * Marks entry. MCQ answers are auto-graded with negative marking; the
     * written/practical portion is entered as a manual total.
     */
    public function edit(Exam $exam)
    {
        $this->scoped($exam);

        $exam->load('questions');
        $students = $exam->students()->get();

        ExamResult::generateSheetFor($exam);

        $results = $exam->results()->with('answers')->get()->keyBy('student_id');

        return view('exams.marks', [
            'exam' => $exam,
            'students' => $students,
            'results' => $results,
            'mcq' => $exam->questions->where('question_type', ExamQuestion::TYPE_MCQ)->values(),
            'manualTotal' => round($exam->total_marks - $exam->mcqTotalMarks(), 2),
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->scoped($exam);

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*.*' => ['nullable', 'in:a,b,c,d'],
            'manual' => ['nullable', 'array'],
            'manual.*' => ['nullable', 'numeric', 'min:0'],
            'absent' => ['nullable', 'array'],
            'absent.*' => ['nullable', 'integer'],
        ]);

        $exam->load('questions');
        $mcq = $exam->questions->where('question_type', ExamQuestion::TYPE_MCQ)->values();
        $mcqIds = $mcq->pluck('id');
        $manualCeiling = round((float) $exam->total_marks - (float) $mcq->sum('marks'), 2);
        $absentIds = array_map('intval', array_filter(
            $validated['absent'] ?? [],
            fn ($value) => $value !== null && $value !== ''
        ));

        $saved = 0;

        foreach ($exam->students()->get() as $student) {
            $result = ExamResult::forStudent($exam, $student);
            $result->total_marks = $exam->total_marks;

            if (in_array($student->id, $absentIds, false)) {
                $result->answers()->delete();
                $result->markAbsent();
                $result->entered_by = Auth::id();
                $result->entered_at = now();
                $result->save();
                $saved++;

                continue;
            }

            foreach ($mcq as $question) {
                $given = $validated['answers'][$student->id][$question->id] ?? null;

                $result->answers()->updateOrCreate(
                    ['exam_question_id' => $question->id],
                    [
                        'given_answer' => $given ?: null,
                        'is_correct' => $question->isCorrect($given),
                        'marks_awarded' => $question->awardFor($given),
                    ]
                );
            }

            $mcqAwarded = $mcqIds->isEmpty()
                ? 0.0
                : (float) $result->answers()->whereIn('exam_question_id', $mcqIds)->sum('marks_awarded');

            $manual = (float) ($validated['manual'][$student->id] ?? 0);
            $manual = max(min($manual, $manualCeiling), 0);

            // Negative marks from wrong MCQ answers offset the manual total;
            // only the combined total is floored at zero.
            $result->obtained_marks = round(max($mcqAwarded + $manual, 0), 2);
            $result->is_absent = false;
            $result->entered_by = Auth::id();
            $result->entered_at = now();
            $result->applyGrade();
            $result->save();

            $saved++;
        }

        return redirect()->route('exams.results', $exam)
            ->with('success', "Marks saved for {$saved} student(s).");
    }

    /**
     * Merit list with pass/fail tally.
     */
    public function results(Exam $exam)
    {
        $this->scoped($exam);

        $exam->load('batch', 'course');
        $results = $exam->results()->with('student')->get()
            ->sortBy(fn (ExamResult $r) => [$r->is_absent ? 1 : 0, -(float) $r->obtained_marks])
            ->values();

        $graded = $results->where('is_absent', false);

        return view('exams.results', [
            'exam' => $exam,
            'results' => $results,
            'summary' => [
                'total' => $results->count(),
                'passed' => $graded->filter(fn ($r) => $r->is_pass)->count(),
                'failed' => $graded->reject(fn ($r) => $r->is_pass)->count(),
                'absent' => $results->where('is_absent', true)->count(),
                'average' => $graded->isEmpty() ? 0 : round($graded->avg(fn ($r) => (float) $r->percentage), 2),
                'pass_rate' => $graded->isEmpty() ? 0 : round(($graded->filter(fn ($r) => $r->is_pass)->count() / $graded->count()) * 100, 2),
            ],
        ]);
    }

    public function resultsCsv(Exam $exam)
    {
        $this->scoped($exam);

        $rows = [['Merit', 'Reference No', 'Student', 'Batch', 'Obtained', 'Total', '%', 'Grade', 'Point', 'Result', 'Absent']];

        $results = $exam->results()->with('student')->get()
            ->sortBy(fn (ExamResult $r) => [$r->is_absent ? 1 : 0, -(float) $r->obtained_marks]);

        foreach ($results as $result) {
            $rows[] = [
                $result->meritPosition() ?: '-',
                $result->student->reference_no,
                $result->student->name_en,
                $exam->batch?->name,
                $result->obtained_marks,
                $result->total_marks,
                $result->percentage,
                $result->grade_letter,
                $result->grade_point,
                $result->is_pass ? 'Pass' : 'Fail',
                $result->is_absent ? 'Yes' : 'No',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'results-'.$exam->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Printable report card for one student in one exam.
     */
    public function reportCard(Exam $exam, Student $student)
    {
        $this->scoped($exam);

        abort_if($student->institute_id !== Auth::user()->institute_id, 403);

        $exam->load(['batch', 'course', 'project']);
        $result = ExamResult::forStudent($exam, $student);
        $result->applyGrade();
        $result->save();

        return view('exams.report-card', [
            'exam' => $exam,
            'student' => $student,
            'result' => $result,
            'institute' => Auth::user()->institute,
            'attendance' => $student->attendanceSummary(),
            'cgpa' => ExamResult::cgpaFor($student->id, $exam->institute_id),
        ]);
    }

    private function scoped(Exam $exam): Exam
    {
        abort_if($exam->institute_id !== Auth::user()->institute_id, 403);

        return $exam;
    }
}
