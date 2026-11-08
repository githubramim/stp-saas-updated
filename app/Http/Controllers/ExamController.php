<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }

    public function index(Request $request)
    {
        $batches = Batch::where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get();

        $exams = Exam::with(['batch', 'course'])
            ->withCount(['questions', 'results'])
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->orderByDesc('exam_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $project = Project::find($this->getProjectId());

        return view('exams.index', compact('exams', 'batches', 'project'));
    }

    public function create()
    {
        $batches = Batch::with('course')
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('exams.create', compact('batches', 'project'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExam($request);

        $batch = Batch::findOrFail($validated['batch_id']);
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $exam = Exam::create([
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $batch->project_id,
            'course_id' => $batch->course_id,
            'batch_id' => $batch->id,
            'title' => $validated['title'],
            'title_bn' => $validated['title_bn'] ?? null,
            'exam_type' => Exam::TYPE_WRITTEN,
            'exam_date' => $validated['exam_date'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => Auth::id(),
        ]);

        $total = $this->syncQuestions($exam, $validated['questions'] ?? []);

        $exam->update([
            'total_marks' => $total,
            'pass_marks' => $total > 0 ? round($total * 0.40, 2) : 0,
            'exam_type' => $this->deriveType($exam->fresh()->questions),
        ]);

        return redirect()->route('exams.show', $exam)
            ->with('success', 'Exam created with '.$total.' total marks.');
    }

    public function show(Exam $exam)
    {
        $this->scoped($exam);

        $exam->load('questions');
        $results = $exam->results()->with('student')->get();
        $graded = $results->where('is_absent', false);
        $passed = $graded->filter(fn ($r) => $r->is_pass)->count();

        return view('exams.show', [
            'exam' => $exam,
            'examResults' => $results,
            'summary' => [
                'students' => $exam->students()->count(),
                'graded' => $graded->count(),
                'passed' => $passed,
                'failed' => $graded->count() - $passed,
                'absent' => $results->where('is_absent', true)->count(),
                'average' => $graded->isEmpty() ? 0 : round($graded->avg(fn ($r) => (float) $r->percentage), 2),
                'highest' => $graded->isEmpty() ? 0 : round($graded->max(fn ($r) => (float) $r->obtained_marks), 2),
            ],
        ]);
    }

    public function edit(Exam $exam)
    {
        $this->scoped($exam);

        $batches = Batch::with('course')
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('exams.edit', [
            'exam' => $exam->load('questions'),
            'batches' => $batches,
            'project' => $project,
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->scoped($exam);

        abort_if($exam->isPublished(), 403, 'Revert the exam to draft before editing questions.');

        $validated = $this->validateExam($request);

        $batch = Batch::findOrFail($validated['batch_id']);
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $total = $this->syncQuestions($exam, $validated['questions'] ?? []);

        $exam->update([
            'batch_id' => $batch->id,
            'project_id' => $batch->project_id,
            'course_id' => $batch->course_id,
            'title' => $validated['title'],
            'title_bn' => $validated['title_bn'] ?? null,
            'exam_date' => $validated['exam_date'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'total_marks' => $total,
            'pass_marks' => $validated['pass_marks'] ?? ($total > 0 ? round($total * 0.40, 2) : 0),
        ]);

        $exam->update(['exam_type' => $this->deriveType($exam->fresh()->questions)]);

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function publish(Exam $exam)
    {
        $this->scoped($exam);

        abort_if($exam->questions->isEmpty(), 422, 'Add at least one question before publishing.');

        ExamResult::generateSheetFor($exam->fresh());

        $exam->update([
            'status' => Exam::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return redirect()->route('exams.results', $exam)
            ->with('success', 'Results published. Students can now view them from the portal.');
    }

    public function revert(Exam $exam)
    {
        $this->scoped($exam);

        $exam->update(['status' => Exam::STATUS_DRAFT, 'published_at' => null]);

        return redirect()->route('exams.show', $exam)->with('success', 'Exam reverted to draft.');
    }

    public function destroy(Exam $exam)
    {
        $this->scoped($exam);

        abort_if($exam->isPublished(), 403, 'Revert the exam to draft before deleting it.');

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    private function validateExam(Request $request): array
    {
        return $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'title' => 'required|string|max:255',
            'title_bn' => 'nullable|string|max:255',
            'exam_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'instructions' => 'nullable|string|max:2000',
            'pass_marks' => 'nullable|numeric|min:0',
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => 'required|string|max:2000',
            'questions.*.question_type' => ['required', 'in:mcq,written,practical'],
            'questions.*.marks' => ['required', 'numeric', 'min:0.25', 'max:1000'],
            'questions.*.negative_marks' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'questions.*.option_a' => 'nullable|string|max:1000',
            'questions.*.option_b' => 'nullable|string|max:1000',
            'questions.*.option_c' => 'nullable|string|max:1000',
            'questions.*.option_d' => 'nullable|string|max:1000',
            'questions.*.correct_option' => ['nullable', 'in:a,b,c,d'],
        ], [
            'questions.required' => 'Add at least one question.',
            'questions.min' => 'Add at least one question.',
        ]);
    }

    /**
     * Replace the question set and return the new total marks.
     */
    private function syncQuestions(Exam $exam, array $questions): float
    {
        $exam->questions()->delete();
        $total = 0.0;

        foreach ($questions as $index => $data) {
            $marks = round((float) $data['marks'], 2);
            $total += $marks;

            ExamQuestion::create([
                'exam_id' => $exam->id,
                'question' => $data['question'],
                'question_type' => $data['question_type'],
                'option_a' => $data['option_a'] ?? null,
                'option_b' => $data['option_b'] ?? null,
                'option_c' => $data['option_c'] ?? null,
                'option_d' => $data['option_d'] ?? null,
                'correct_option' => ($data['question_type'] ?? null) === ExamQuestion::TYPE_MCQ
                    ? ($data['correct_option'] ?? null)
                    : null,
                'negative_marks' => round((float) ($data['negative_marks'] ?? 0), 2),
                'marks' => $marks,
                'display_order' => $index,
            ]);
        }

        return round($total, 2);
    }

    private function deriveType($questions): string
    {
        $types = $questions->pluck('question_type')->unique();

        if ($types->count() === 1) {
            return $types->first();
        }

        if ($types->contains(ExamQuestion::TYPE_MCQ)) {
            return 'mixed';
        }

        return Exam::TYPE_WRITTEN;
    }

    private function scoped(Exam $exam): Exam
    {
        abort_if($exam->institute_id !== Auth::user()->institute_id, 403);

        return $exam;
    }
}
