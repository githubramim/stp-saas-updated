<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudyMaterialController extends Controller
{
    public function index(Request $request)
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = StudyMaterial::with(['course', 'batch', 'uploadedBy'])
            ->where('institute_id', $instituteId)
            ->whereHas('course', fn($q) => $q->where('project_id', $projectId));

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->batch_id) {
            $query->where('batch_id', $request->batch_id);
        }

        $materials = $query->orderBy('created_at', 'desc')->paginate(20);
        $courses = Course::where('institute_id', $instituteId)
            ->where('project_id', $projectId)
            ->get();

        $batches = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->where('project_id', $projectId)
            ->get();

        return view('study-materials.index', compact('materials', 'courses', 'batches'));
    }

    public function create()
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $courses = Course::where('institute_id', $instituteId)
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->get();

        $batches = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->where('project_id', $projectId)
            ->where('status', 'running')
            ->get();

        return view('study-materials.create', compact('courses', 'batches'));
    }

    public function store(Request $request)
    {
        $instituteId = Auth::user()->institute_id;

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'nullable|exists:batches,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_name' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);

        $course = Course::findOrFail($request->course_id);
        abort_if($course->institute_id !== $instituteId, 403);

        $file = $request->file('file');
        $path = $file->store('study-materials', 'public');

        StudyMaterial::create([
            'institute_id' => $instituteId,
            'course_id' => $request->course_id,
            'batch_id' => $request->batch_id,
            'uploaded_by' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'module_name' => $request->module_name,
        ]);

        return redirect()->route('study-materials.index')
            ->with('success', 'Study material uploaded successfully.');
    }

    public function download(StudyMaterial $studyMaterial)
    {
        abort_if($studyMaterial->institute_id !== Auth::user()->institute_id, 403);

        return Storage::disk('public')->download($studyMaterial->file_path, $studyMaterial->title . '.' . $studyMaterial->file_type);
    }

    public function destroy(StudyMaterial $studyMaterial)
    {
        abort_if($studyMaterial->institute_id !== Auth::user()->institute_id, 403);

        Storage::disk('public')->delete($studyMaterial->file_path);
        $studyMaterial->delete();

        return redirect()->route('study-materials.index')
            ->with('success', 'Study material deleted successfully.');
    }
}
