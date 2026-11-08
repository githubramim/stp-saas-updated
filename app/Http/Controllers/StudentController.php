<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function showForm(Request $request)
    {
        $institutes = Institute::where('status', 'active')->get();
        $courses = collect();
        $batches = collect();

        if ($request->institute_id) {
            $courses = Course::where('institute_id', $request->institute_id)
                ->where('status', 'active')->get();
        }
        if ($request->course_id) {
            $batches = Batch::where('course_id', $request->course_id)
                ->where('status', 'active')->get();
        }

        return view('apply', compact('institutes', 'courses', 'batches'));
    }

    public function getCourses(Request $request)
    {
        $courses = Course::where('institute_id', $request->institute_id)
            ->where('status', 'active')->get(['id', 'name']);
        return response()->json($courses);
    }

    public function getBatches(Request $request)
    {
        $batches = Batch::where('course_id', $request->course_id)
            ->where('status', 'active')->get(['id', 'name']);
        return response()->json($batches);
    }

    public function store(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'required|exists:batches,id',
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'father_name_en' => 'required|string|max:255',
            'father_name_bn' => 'nullable|string|max:255',
            'mother_name_en' => 'required|string|max:255',
            'mother_name_bn' => 'nullable|string|max:255',
            'contact' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'religion' => 'nullable|string',
            'nationality' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'id_type' => 'required|string',
            'id_no' => 'required|string',
            'occupation' => 'nullable|string',
            'guardian_contact' => 'required|string|max:20',
            'guardian_relation' => 'required|string|max:100',
            'present_village' => 'nullable|string|max:255',
            'present_district' => 'nullable|string|max:255',
        ]);

        $course = Course::findOrFail($request->course_id);
        abort_if($course->institute_id != $request->institute_id, 400);

        $batch = Batch::findOrFail($request->batch_id);
        abort_if($batch->course_id != $request->course_id, 400);

        $referenceNo = 'STP-' . strtoupper(Str::random(8));

        $data = $request->except(['_token', 'photo_base64', 'signature_base64', 'nid_base64', 'nid_file']);
        $data['reference_no'] = $referenceNo;
        $data['status'] = 'applied';

        $data['photo_path'] = $this->saveBase64Image($request->photo_base64, 'photos', 300, 300);
        $data['signature_path'] = $this->saveBase64Image($request->signature_base64, 'signatures', 300, 80);

        if ($request->hasFile('nid_file')) {
            $data['nid_path'] = $request->file('nid_file')->store('nids', 'public');
        }

        Student::create($data);

        return redirect()->route('apply.success', $referenceNo);
    }

    private function saveBase64Image(?string $base64, string $folder, int $maxW, int $maxH): ?string
    {
        if (!$base64) return null;

        if (str_starts_with($base64, 'data:image')) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        $imageData = base64_decode($base64);
        if ($imageData === false) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $filename = Str::random(20) . '.' . $ext;
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    public function success($referenceNo)
    {
        return view('apply-success', compact('referenceNo'));
    }
}
