<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\InternalEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalEvaluationController extends Controller
{
    public function index()
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $evaluations = InternalEvaluation::with(['batch.course', 'trainer', 'student'])
            ->where('institute_id', $instituteId)
            ->whereHas('batch', fn($q) => $q->where('project_id', $projectId))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('evaluations.index', compact('evaluations'));
    }

    public function create()
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $batches = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->where('project_id', $projectId)
            ->get();

        $trainers = User::where('institute_id', $instituteId)
            ->where('role', 'trainer')
            ->get();

        return view('evaluations.create', compact('batches', 'trainers'));
    }

    public function store(Request $request)
    {
        $instituteId = Auth::user()->institute_id;

        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'trainer_id' => 'required|exists:users,id',
            'rating_score' => 'required|integer|min:1|max:5',
            'review_comments' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        abort_if($batch->institute_id !== $instituteId, 403);

        InternalEvaluation::create([
            'institute_id' => $instituteId,
            'batch_id' => $request->batch_id,
            'trainer_id' => $request->trainer_id,
            'student_id' => Auth::user()->isTrainer() ? null : $request->student_id,
            'rating_score' => $request->rating_score,
            'review_comments' => $request->review_comments,
            'is_anonymous' => $request->is_anonymous ?? true,
        ]);

        return redirect()->route('evaluations.index')
            ->with('success', 'Evaluation submitted successfully.');
    }

    public function report(Request $request)
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $trainers = User::where('institute_id', $instituteId)
            ->where('role', 'trainer')
            ->get();

        $selectedTrainerId = $request->trainer_id;

        $evaluations = InternalEvaluation::with(['batch.course', 'trainer'])
            ->where('institute_id', $instituteId)
            ->whereHas('batch', fn($q) => $q->where('project_id', $projectId));

        if ($selectedTrainerId) {
            $evaluations->where('trainer_id', $selectedTrainerId);
        }

        $evaluations = $evaluations->get();

        $avgRating = $evaluations->avg('rating_score');

        $ratingsByTrainer = $evaluations->groupBy('trainer_id')->map(function ($items) {
            return [
                'trainer' => $items->first()->trainer,
                'avg_rating' => round($items->avg('rating_score'), 1),
                'count' => $items->count(),
            ];
        });

        return view('evaluations.report', compact('trainers', 'selectedTrainerId', 'evaluations', 'avgRating', 'ratingsByTrainer'));
    }
}
