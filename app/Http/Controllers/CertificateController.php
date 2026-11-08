<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['student', 'exam'])->latest()->paginate(20);
        return view('certificates.index', compact('certificates'));
    }

    public function create()
    {
        return view('certificates.create');
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
