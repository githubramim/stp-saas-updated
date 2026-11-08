<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        $projects = Project::where('institute_id', Auth::user()->institute_id)
            ->withCount(['courses', 'batches', 'students'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        return view('projects.create');
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        Project::create([
            'institute_id' => Auth::user()->institute_id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $project->update($request->only(['name', 'name_bn', 'code', 'description', 'status']));

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function dashboard(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Project Overview Dashboards.');
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $courseCount = Course::where('project_id', $project->id)->count();
        $batchCount = Batch::where('project_id', $project->id)->count();
        $studentCount = Student::where('project_id', $project->id)->count();
        $appliedCount = Student::where('project_id', $project->id)->where('status', 'applied')->count();
        $admittedCount = Student::where('project_id', $project->id)->where('status', 'admitted')->count();

        $courses = Course::where('project_id', $project->id)->orderBy('name')->get();
        $batches = Batch::with('course')
            ->where('project_id', $project->id)
            ->orderBy('name')
            ->get();

        $students = Student::with(['course', 'batch'])
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-dashboard', compact(
            'project', 'courses', 'batches', 'students',
            'courseCount', 'batchCount', 'studentCount',
            'appliedCount', 'admittedCount'
        ));
    }

    public function switch(Request $request, Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Project switching.');
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        session(['current_project_id' => $project->id]);
        $redirect = $request->input('redirect', url()->previous());
        return redirect($redirect)->with('success', 'Switched to project: ' . $project->name);
    }

    public function switchContext(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Project switching.');

        $projectId = $request->input('project_id');
        if ($projectId === 'all' || empty($projectId)) {
            session()->forget('current_project_id');
            return back()->with('success', 'Switched to: All Projects View (সব প্রজেক্ট)');
        }

        $project = Project::where('institute_id', Auth::user()->institute_id)->findOrFail($projectId);
        session(['current_project_id' => $project->id]);
        return back()->with('success', "Switched to Project: {$project->name}");
    }
}
