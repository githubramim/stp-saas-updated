<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionController extends Controller
{
    public function applications()
    {
        $students = Student::with(['course', 'batch'])
            ->where('institute_id', Auth::user()->institute_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admissions.applications', compact('students'));
    }

    public function show(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        return view('admissions.show', compact('student'));
    }

    public function print(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        $student->load(['course', 'batch', 'institute', 'project']);
        return view('admissions.print', compact('student'));
    }

    public function approve(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        $student->update(['status' => 'admitted']);
        return back()->with('success', 'Student admitted successfully.');
    }

    public function reject(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        $student->update(['status' => 'rejected']);
        return back()->with('success', 'Student rejected.');
    }

    public function changeBatch(Request $request, Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);

        $request->validate(['batch_id' => 'required|exists:batches,id']);
        $batch = Batch::findOrFail($request->batch_id);
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $student->update(['batch_id' => $request->batch_id]);
        return back()->with('success', 'Batch updated successfully.');
    }

    public function removeFromBatch(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        $student->update(['batch_id' => null, 'status' => 'applied']);
        return back()->with('success', 'Student removed from batch.');
    }

    public function students()
    {
        $students = Student::with(['course', 'batch'])
            ->where('institute_id', Auth::user()->institute_id)
            ->where('status', 'admitted')
            ->orderBy('name_en')
            ->get();

        $batches = Batch::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')->get();

        return view('admissions.students', compact('students', 'batches'));
    }
}
