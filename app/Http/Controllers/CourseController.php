<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CourseController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }
    public function index()
    {
        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('courses.index', compact('courses', 'project'));
    }

    public function create()
    {
        $project = Project::find($this->getProjectId());

        return view('courses.create', compact('project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            'fee' => 'required|numeric|min:0',
            'seat_capacity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        Course::create([
            'institute_id' => Auth::user()->institute_id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'duration' => $request->duration,
            'fee' => $request->fee,
            'seat_capacity' => $request->seat_capacity,
            'description' => $request->description,
        ]);

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);
        $project = Project::find($this->getProjectId());
        return view('courses.edit', compact('course', 'project'));
    }

    public function update(Request $request, Course $course)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            'fee' => 'required|numeric|min:0',
            'seat_capacity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $course->update($request->only([
            'name', 'name_bn', 'duration', 'fee', 'seat_capacity', 'description',
        ]));

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function inlineStore(Request $request, Project $project)
    {
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            'fee' => 'required|numeric|min:0',
            'seat_capacity' => 'required|integer|min:0',
        ]);

        Course::create([
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $project->id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'duration' => $request->duration,
            'fee' => $request->fee,
            'seat_capacity' => $request->seat_capacity,
        ]);

        return Redirect::to(route('projects.dashboard', $project) . '#courses')
            ->with('success', 'Course created successfully.');
    }

    public function destroy(Course $course)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
