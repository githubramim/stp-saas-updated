<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Project;
use App\Services\TamListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }

    /**
     * TAM options shared by the create modal and the edit form.
     *
     * @return array<string, mixed>
     */
    private function tamOptions(TamListService $tam): array
    {
        return [
            'tamOccupations' => $tam->occupations(),
            'tamSectors' => $tam->sectors(),
            'tamLevels' => $tam->levels(),
            'tamOccupationSectors' => $tam->occupationSectors(),
            'tamAvailable' => $tam->hasData(),
        ];
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where('institute_id', Auth::user()->institute_id),
            ],
            'course_code' => 'nullable|string|max:50',
            'ntvqf_level' => 'nullable|string|max:20',
            'tam_sector' => 'nullable|string|max:255',
            'tam_occupation' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            'duration_hours' => 'nullable|integer|min:0',
            'fee' => 'required|numeric|min:0',
            'seat_capacity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Server-side safety net for the TAM auto-fill. The form also fills these
     * fields with JavaScript, but the values are resolved again here so the
     * nominal hour is always correct even when JS is unavailable.
     */
    private function applyTamDefaults(array $data, TamListService $tam): array
    {
        $occupation = trim((string) ($data['tam_occupation'] ?? ''));

        if ($occupation === '') {
            return $data;
        }

        $level = $data['ntvqf_level'] ?? null;
        $match = $tam->lookup($occupation, $level);

        if (empty($data['tam_sector']) && $match['sector']) {
            $data['tam_sector'] = $match['sector'];
        }

        if (($level !== null && $level !== '') && empty($data['duration_hours']) && $match['duration_hours']) {
            $data['duration_hours'] = $match['duration_hours'];
        }

        if (! empty($data['duration_hours']) && empty($data['duration'])) {
            $data['duration'] = $data['duration_hours'].' hours';
        }

        return $data;
    }

    public function index(TamListService $tam)
    {
        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('courses.index', array_merge(
            compact('courses', 'project'),
            $this->tamOptions($tam)
        ));
    }

    public function create()
    {
        $project = Project::find($this->getProjectId());

        return view('courses.create', compact('project'));
    }

    public function store(Request $request, TamListService $tam)
    {
        $data = $this->applyTamDefaults($request->validate($this->validationRules()), $tam);

        Course::create(array_merge($data, [
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $data['project_id'] ?? $this->getProjectId(),
        ]));

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function edit(Course $course, TamListService $tam)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);
        $project = Project::find($this->getProjectId());

        return view('courses.edit', array_merge(
            compact('course', 'project'),
            $this->tamOptions($tam)
        ));
    }

    public function update(Request $request, Course $course, TamListService $tam)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);

        $data = $this->applyTamDefaults($request->validate($this->validationRules()), $tam);

        $course->update($data);

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function inlineStore(Request $request, Project $project, TamListService $tam)
    {
        abort_if($project->institute_id !== Auth::user()->institute_id, 403);

        $data = $this->applyTamDefaults($request->validate($this->validationRules()), $tam);

        Course::create(array_merge($data, [
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $project->id,
        ]));

        return Redirect::to(route('projects.dashboard', $project).'#courses')
            ->with('success', 'Course created successfully.');
    }

    /**
     * JSON lookup used by the Occupation + Level auto-fill on the course forms.
     */
    public function tamLookup(Request $request, TamListService $tam)
    {
        $occupation = trim((string) $request->query('occupation', ''));

        if ($occupation === '') {
            return response()->json([
                'occupation' => null,
                'sector' => null,
                'level' => null,
                'duration_hours' => null,
                'durations' => [],
                'sectors' => [],
                'levels' => [],
                'rows' => [],
            ]);
        }

        return response()->json($tam->lookup($occupation, $request->query('level')));
    }

    public function destroy(Course $course)
    {
        abort_if($course->institute_id !== Auth::user()->institute_id, 403);
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
