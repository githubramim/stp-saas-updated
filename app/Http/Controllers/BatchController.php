<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class BatchController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }
    public function index()
    {
        $batches = Batch::with('course')
            ->where('institute_id', Auth::user()->institute_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $project = Project::find($this->getProjectId());

        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        return view('batches.index', compact('batches', 'project', 'courses'));
    }

    public function create()
    {
        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('batches.create', compact('courses', 'project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|string|max:50',
            'seat_capacity' => 'required|integer|min:0',
        ]);

        $course = Course::findOrFail($request->course_id);
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);

        Batch::create([
            'institute_id' => Auth::user()->institute_id,
            'course_id' => $request->course_id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift' => $request->shift,
            'seat_capacity' => $request->seat_capacity,
        ]);

        return redirect()->route('batches.index')->with('success', 'Batch created successfully.');
    }

    public function edit(Batch $batch)
    {
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('batches.edit', compact('batch', 'courses', 'project'));
    }

    public function update(Request $request, Batch $batch)
    {
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|string|max:50',
            'seat_capacity' => 'required|integer|min:0',
        ]);

        $course = Course::findOrFail($request->course_id);
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);

        $batch->update($request->only([
            'course_id', 'name', 'name_bn', 'start_date', 'end_date', 'shift', 'seat_capacity',
        ]));

        return redirect()->route('batches.index')->with('success', 'Batch updated successfully.');
    }

    public function inlineStore(Request $request, Project $project)
    {
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|string|max:50',
            'seat_capacity' => 'required|integer|min:0',
        ]);

        $course = Course::findOrFail($request->course_id);
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);
        abort_if($course->project_id !== $project->id, 403);

        Batch::create([
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $project->id,
            'course_id' => $request->course_id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift' => $request->shift,
            'seat_capacity' => $request->seat_capacity,
        ]);

        return Redirect::to(route('projects.dashboard', $project) . '#batches')
            ->with('success', 'Batch created successfully.');
    }

    public function destroy(Batch $batch)
    {
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);
        $batch->delete();

        return redirect()->route('batches.index')->with('success', 'Batch deleted successfully.');
    }
}
