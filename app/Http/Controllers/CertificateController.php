<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['student', 'exam'])->latest()->paginate(20);

        $instituteId = auth()->user()->institute_id;
        $students = Student::where('institute_id', $instituteId)->orderBy('name_en')->get();
        $exams = Exam::where('institute_id', $instituteId)->latest()->get();

        return view('certificates.index', compact('certificates', 'students', 'exams'));
    }

    public function create()
    {
        $instituteId = auth()->user()->institute_id;

        $students = Student::where('institute_id', $instituteId)
            ->orderBy('name_en')
            ->get();

        $exams = Exam::where('institute_id', $instituteId)
            ->latest()
            ->get();

        return view('certificates.create', compact('students', 'exams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'exam_id' => 'nullable|exists:exams,id',
            'title' => 'required|string|max:255',
            'issued_date' => 'nullable|date',
        ]);
        $validated['certificate_no'] = 'CERT-' . strtoupper(uniqid());
        $validated['verification_code'] = strtoupper(bin2hex(random_bytes(8)));
        $validated['institute_id'] = auth()->user()->institute_id ?? null;
        Certificate::create($validated);
        return redirect()->route('certificates.index')->with('success', 'Certificate created.');
    }

    public function show(Certificate $certificate)
    {
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        return view('certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issued_date' => 'nullable|date',
        ]);
        $certificate->update($validated);
        return redirect()->route('certificates.index')->with('success', 'Updated.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return redirect()->route('certificates.index')->with('success', 'Deleted.');
    }
}
