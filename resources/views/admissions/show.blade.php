<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Student Details') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $student->name_en }}</h3>
                            <p class="text-sm text-gray-500">Ref: {{ $student->reference_no }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded
                            @if($student->status === 'admitted') bg-green-100 text-green-700
                            @elseif($student->status === 'rejected') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">{{ ucfirst($student->status) }}</span>
                    </div>

                    {{-- Personal --}}
                    <h4 class="font-semibold text-indigo-700 border-b pb-1 mb-3">Personal Information</h4>
                    <dl class="grid grid-cols-3 gap-4 text-sm mb-5">
                        <div><dt class="text-gray-500">Name (EN)</dt><dd>{{ $student->name_en }}</dd></div>
                        <div><dt class="text-gray-500">Name (BN)</dt><dd>{{ $student->name_bn ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Gender</dt><dd>{{ $student->gender }}</dd></div>
                        <div><dt class="text-gray-500">Father's Name</dt><dd>{{ $student->father_name_en }}</dd></div>
                        <div><dt class="text-gray-500">Mother's Name</dt><dd>{{ $student->mother_name_en }}</dd></div>
                        <div><dt class="text-gray-500">DOB</dt><dd>{{ $student->dob }}</dd></div>
                        <div><dt class="text-gray-500">Mobile</dt><dd>{{ $student->contact }}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd>{{ $student->email ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Nationality</dt><dd>{{ $student->nationality ?? 'Bangladeshi' }}</dd></div>
                        <div><dt class="text-gray-500">Religion</dt><dd>{{ $student->religion ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Blood Group</dt><dd>{{ $student->blood_group ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Occupation</dt><dd>{{ $student->occupation ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">ID</dt><dd>{{ $student->id_type }}: {{ $student->id_no }}</dd></div>
                        <div><dt class="text-gray-500">Guardian</dt><dd>{{ $student->guardian_relation }}: {{ $student->guardian_contact }}</dd></div>
                        <div><dt class="text-gray-500">Course</dt><dd>{{ $student->course?->name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Batch</dt><dd>{{ $student->batch?->name ?? '—' }}</dd></div>
                    </dl>

                    {{-- Address --}}
                    <h4 class="font-semibold text-purple-700 border-b pb-1 mb-3">Present Address</h4>
                    <dl class="grid grid-cols-3 gap-4 text-sm mb-5">
                        <div><dt class="text-gray-500">Village/Road</dt><dd>{{ $student->present_village ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Post Office</dt><dd>{{ $student->present_po ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Upazila</dt><dd>{{ $student->present_upazila ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">District</dt><dd>{{ $student->present_district ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Division</dt><dd>{{ $student->present_division ?? '—' }}</dd></div>
                    </dl>

                    <h4 class="font-semibold text-purple-700 border-b pb-1 mb-3">Permanent Address</h4>
                    <dl class="grid grid-cols-3 gap-4 text-sm mb-5">
                        <div><dt class="text-gray-500">Village/Road</dt><dd>{{ $student->perm_village ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Post Office</dt><dd>{{ $student->perm_po ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Upazila</dt><dd>{{ $student->perm_upazila ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">District</dt><dd>{{ $student->perm_district ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Division</dt><dd>{{ $student->perm_division ?? '—' }}</dd></div>
                    </dl>

                    {{-- Education --}}
                    <h4 class="font-semibold text-amber-700 border-b pb-1 mb-3">Education</h4>
                    <dl class="grid grid-cols-3 gap-4 text-sm mb-5">
                        <div><dt class="text-gray-500">Degree</dt><dd>{{ $student->edu_degree ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Institute</dt><dd>{{ $student->edu_institute ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Year</dt><dd>{{ $student->edu_year ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">CGPA</dt><dd>{{ $student->edu_cgpa ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Address</dt><dd>{{ $student->edu_address ?? '—' }}</dd></div>
                    </dl>

                    {{-- Documents --}}
                    @if($student->photo_path || $student->signature_path || $student->nid_path)
                    <h4 class="font-semibold text-rose-700 border-b pb-1 mb-3">Documents</h4>
                    <div class="grid grid-cols-3 gap-4 text-sm mb-5">
                        @if($student->photo_path)
                        <div>
                            <dt class="text-gray-500 mb-1">Photo</dt>
                            <dd><img src="{{ asset('storage/' . $student->photo_path) }}" class="w-24 h-24 object-cover rounded-lg border"></dd>
                        </div>
                        @endif
                        @if($student->signature_path)
                        <div>
                            <dt class="text-gray-500 mb-1">Signature</dt>
                            <dd><img src="{{ asset('storage/' . $student->signature_path) }}" class="w-32 h-10 object-contain rounded border"></dd>
                        </div>
                        @endif
                        @if($student->nid_path)
                        <div>
                            <dt class="text-gray-500 mb-1">NID/Birth</dt>
                            <dd><img src="{{ asset('storage/' . $student->nid_path) }}" class="w-full h-24 object-contain rounded border"></dd>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="mt-6 flex gap-3">
                        @if($student->status === 'applied')
                            <form action="{{ route('admissions.approve', $student) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Approve Admission</button>
                            </form>
                            <form action="{{ route('admissions.reject', $student) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reject</button>
                            </form>
                        @endif
                        <a href="{{ route('admissions.print', $student) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <i class="bi bi-printer"></i> Print Form
                        </a>
                        <a href="{{ route('admissions.applications') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
