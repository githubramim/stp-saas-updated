<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\InternalEvaluation;
use App\Models\Student;
use App\Models\StudyMaterial;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function __construct(private readonly FeeService $fees) {}

    /**
     * Show public portal search form.
     */
    public function index(): View
    {
        return view('portal.index');
    }

    /**
     * Look up student results and attendance by Reference No and DOB.
     */
    public function show(Request $request): View
    {
        $validated = $request->validate([
            'reference_no' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
        ]);

        // Public lookup: ignore the staff branch scope that may be active in
        // the same browser. Reference numbers are globally unique.
        $student = Student::withoutGlobalScope('branch')
            ->where('reference_no', $validated['reference_no'])
            ->first();

        if (! $student || $student->dob?->toDateString() !== Carbon::parse($validated['dob'])->toDateString()) {
            return back()
                ->withInput()
                ->withErrors(['reference_no' => 'No student found with that registration number and date of birth.']);
        }

        $results = ExamResult::with(['exam' => ['batch', 'course']])
            ->where('student_id', $student->id)
            ->whereHas('exam', fn ($q) => $q->where('status', Exam::STATUS_PUBLISHED))
            ->get()
            ->sortByDesc(fn (ExamResult $r) => $r->exam?->exam_date)
            ->values();

        return view('portal.show', [
            'student' => $student,
            'results' => $results,
            'attendance' => $student->attendanceSummary(),
            'cgpa' => ExamResult::cgpaFor($student->id, $student->institute_id),
            'unpublishedCount' => ExamResult::where('student_id', $student->id)
                ->whereHas('exam', fn ($q) => $q->where('status', '!=', Exam::STATUS_PUBLISHED))
                ->count(),
            ...$this->fees->portalSummary($student),
        ]);
    }

    /**
     * Verify student by Reference No or Contact Phone.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'query_input' => 'required|string|max:100',
        ]);

        $input = trim($request->query_input);

        // Public lookup must ignore any staff branch scope active in the same
        // browser. Grouping the OR conditions keeps the lookup unambiguous.
        $matches = Student::withoutGlobalScope('branch')
            ->with(['course', 'batch.trainer', 'institute', 'project'])
            ->where(function ($q) use ($input) {
                $q->where('reference_no', $input)->orWhere('contact', $input);
            })
            ->limit(2)
            ->get();

        if ($matches->isEmpty()) {
            return back()->with('error', 'No student application found for the provided Reference No or Phone Number.');
        }

        if ($matches->count() > 1) {
            return back()->with('error', 'More than one student matches that phone number. Please use your Reference No.');
        }

        $student = $matches->first();

        // Fetch Study Materials for the student's course and batch
        $studyMaterials = collect();
        if ($student->course_id) {
            $studyMaterials = StudyMaterial::withoutGlobalScope('branch')
                ->where('institute_id', $student->institute_id)
                ->where('course_id', $student->course_id)
                ->where(function ($q) use ($student) {
                    $q->whereNull('batch_id')->orWhere('batch_id', $student->batch_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('portal.dashboard', array_merge(
            compact('student', 'studyMaterials'),
            $this->fees->portalSummary($student)
        ));
    }

    /**
     * Download study material from Student Portal.
     */
    public function downloadMaterial(StudyMaterial $studyMaterial)
    {
        if (! Storage::disk('public')->exists($studyMaterial->file_path)) {
            return back()->with('error', 'File not found on server.');
        }

        return Storage::disk('public')->download($studyMaterial->file_path, $studyMaterial->title.'.'.$studyMaterial->file_type);
    }

    /**
     * Submit trainer evaluation/feedback from Student Portal.
     */
    public function submitEvaluation(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'rating_score' => 'required|integer|min:1|max:5',
            'review_comments' => 'nullable|string|max:1000',
        ]);

        $student = Student::withoutGlobalScope('branch')->findOrFail($request->student_id);

        if (! $student->batch_id || ! $student->batch?->trainer_id) {
            return back()->with('error', 'You cannot submit an evaluation because no active batch/trainer is assigned to your record.');
        }

        InternalEvaluation::create([
            'institute_id' => $student->institute_id,
            'batch_id' => $student->batch_id,
            'trainer_id' => $student->batch->trainer_id,
            'student_id' => $student->id,
            'rating_score' => $request->rating_score,
            'review_comments' => $request->review_comments,
            'is_anonymous' => $request->has('is_anonymous'),
        ]);

        return back()->with('success', 'Thank you! Your feedback has been submitted successfully.');
    }
}
