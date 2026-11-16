<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Student;
use App\Support\BranchContext;
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
        $student->update([
            'status' => 'admitted',
            // Admitted students belong to the branch active in the top bar
            // (BRD module #21); without one they fall back to the default.
            'branch_id' => $student->branch_id
                ?? BranchContext::current(Auth::user())?->id
                ?? BranchContext::defaultFor((int) Auth::user()->institute_id)?->id,
        ]);

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

    public function changeBranch(Request $request, Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);

        $request->validate(['branch_id' => 'required|exists:branches,id']);
        $branch = Branch::findOrFail($request->branch_id);
        abort_if($branch->institute_id !== Auth::user()->institute_id, 403);

        $student->update(['branch_id' => $request->branch_id]);

        return back()->with('success', 'Branch updated successfully.');
    }

    public function removeFromBatch(Student $student)
    {
        abort_if($student->institute_id !== Auth::user()->institute_id, 403);
        $student->update(['batch_id' => null, 'status' => 'applied']);

        return back()->with('success', 'Student removed from batch.');
    }

    public function students()
    {
        $students = Student::with(['course', 'batch', 'branch'])
            ->where('institute_id', Auth::user()->institute_id)
            ->where('status', 'admitted')
            ->orderBy('name_en')
            ->get();

        $batches = Batch::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')->get();

        $branches = Branch::where('institute_id', Auth::user()->institute_id)
            ->orderBy('id')->get();

        return view('admissions.students', compact('students', 'batches', 'branches'));
    }
}
