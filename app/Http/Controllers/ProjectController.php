<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Project;
use App\Models\Student;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        // The Project model's branch scope restricts this to the selected branch.
        $projects = Project::where('institute_id', Auth::user()->institute_id)
            ->withCount(['courses', 'batches', 'students'])
            ->orderBy('created_at', 'desc')
            ->get();

        $branches = $this->branches();

        return view('projects.index', compact('projects', 'branches'));
    }

    public function create()
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        $project = new Project;
        $branches = $this->branches();

        return view('projects.create', compact('project', 'branches'));
    }

    private function branches()
    {
        return Branch::where('institute_id', Auth::user()->institute_id)
            ->orderBy('id')
            ->get();
    }

    private function branchRule(): array
    {
        return ['nullable', 'integer', Rule::exists('branches', 'id')->where('institute_id', Auth::user()->institute_id)];
    }

    /**
     * Route model binding runs before the branch scope middleware, so property
     * check the bound project as well. This keeps another branch's project
     * unreachable by guessing its URL.
     */
    private function assertBranch(Project $project): void
    {
        abort_if(
            (int) $project->institute_id !== (int) Auth::user()->institute_id
            || (int) $project->branch_id !== BranchContext::scopeId(),
            404
        );
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'branch_id' => $this->branchRule(),
            'donor_name' => 'nullable|string|max:255',
            'donor_type' => 'nullable|string|max:50',
            'budget_amount' => 'nullable|numeric|min:0',
            'mou_number' => 'nullable|string|max:100',
            'mou_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_trainees' => 'nullable|integer|min:0',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_contact' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('mou_file')) {
            $validated['mou_file_path'] = $request->file('mou_file')->store('mou', 'public');
        }

        unset($validated['mou_file']);

        Project::create(array_merge($validated, [
            'institute_id' => Auth::user()->institute_id,
            // New projects land in the branch currently selected in the top bar.
            'branch_id' => (int) ($validated['branch_id'] ?? BranchContext::scopeId()),
        ]));

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        $this->assertBranch($project);

        $branches = $this->branches();

        return view('projects.edit', compact('project', 'branches'));
    }

    public function update(Request $request, Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        $this->assertBranch($project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'branch_id' => $this->branchRule(),
            'donor_name' => 'nullable|string|max:255',
            'donor_type' => 'nullable|string|max:50',
            'budget_amount' => 'nullable|numeric|min:0',
            'mou_number' => 'nullable|string|max:100',
            'mou_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_trainees' => 'nullable|integer|min:0',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_contact' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('mou_file')) {
            if ($project->mou_file_path) {
                Storage::disk('public')->delete($project->mou_file_path);
            }
            $validated['mou_file_path'] = $request->file('mou_file')->store('mou', 'public');
        }

        unset($validated['mou_file']);

        $project->update($validated);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Master Projects.');
        $this->assertBranch($project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function dashboard(Project $project)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Project Overview Dashboards.');
        $this->assertBranch($project);

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
        $this->assertBranch($project);

        session(['current_project_id' => $project->id]);
        $redirect = $request->input('redirect', url()->previous());

        return redirect($redirect)->with('success', 'Switched to project: '.$project->name);
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
