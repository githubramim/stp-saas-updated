@extends('layouts.apply')

@section('content')

{{-- Step indicator --}}
<div class="flex items-center justify-center gap-0 mb-8">
    <div class="flex items-center">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ request('institute_id') ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white' }}">1</div>
        <span class="ml-2 text-sm font-medium {{ request('institute_id') ? 'text-indigo-600' : 'text-gray-700' }}">Institute</span>
    </div>
    <div class="w-16 h-0.5 mx-3 {{ request('institute_id') ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
    <div class="flex items-center">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ request('course_id') ? 'bg-indigo-600 text-white' : (request('institute_id') ? 'bg-white border-2 border-indigo-600 text-indigo-600' : 'bg-gray-100 text-gray-400') }}">2</div>
        <span class="ml-2 text-sm font-medium {{ request('course_id') ? 'text-indigo-600' : (request('institute_id') ? 'text-indigo-600' : 'text-gray-400') }}">Course</span>
    </div>
    <div class="w-16 h-0.5 mx-3 {{ request('course_id') ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
    <div class="flex items-center">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ request('course_id') ? 'bg-white border-2 border-indigo-600 text-indigo-600' : 'bg-gray-100 text-gray-400' }}">3</div>
        <span class="ml-2 text-sm font-medium {{ request('course_id') ? 'text-indigo-600' : 'text-gray-400' }}">Apply</span>
    </div>
</div>

{{-- Step 1: Select Institute --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 {{ request('institute_id') ? 'opacity-60' : '' }}">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">Step 1: Select Institute</h2>
            <p class="text-xs text-gray-500">Choose the institute you want to apply to</p>
        </div>
    </div>
    <form method="GET" action="{{ route('apply') }}">
        <select name="institute_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" onchange="this.form.submit()">
            <option value="">— Select Institute —</option>
            @foreach($institutes as $inst)
                <option value="{{ $inst->id }}" {{ request('institute_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }} ({{ $inst->code }})</option>
            @endforeach
        </select>
    </form>
</div>

@if(request('institute_id'))
    {{-- Step 2: Select Course --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-5 {{ request('course_id') ? 'opacity-60' : '' }}">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">Step 2: Select Course</h2>
                <p class="text-xs text-gray-500">Choose your desired course</p>
            </div>
        </div>
        <form method="GET" action="{{ route('apply') }}">
            <input type="hidden" name="institute_id" value="{{ request('institute_id') }}">
            <select name="course_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" onchange="this.form.submit()">
                <option value="">— Select Course —</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
@endif

@if(request('course_id'))
    {{-- Step 3: Application Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-5">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">Step 3: Fill Application</h2>
                <p class="text-xs text-gray-500">Complete all required fields below</p>
            </div>
        </div>

        <form method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="institute_id" value="{{ request('institute_id') }}">
            <input type="hidden" name="course_id" value="{{ request('course_id') }}">

            {{--=============== BATCH ===============--}}
            <div class="border-b border-gray-100 pb-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Batch *</label>
                <select name="batch_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    <option value="">— Select Batch —</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }} ({{ $batch->shift ?? 'N/A' }})</option>
                    @endforeach
                </select>
                @error('batch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{--=============== PERSONAL INFO ===============--}}
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personal Information
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name (English) *</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" placeholder="Enter full name in English" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name (Bangla)</label>
                        <input type="text" name="name_bn" value="{{ old('name_bn') }}" placeholder="পূর্ণ নাম বাংলায়" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        @error('name_bn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Father's Name (EN) *</label>
                        <input type="text" name="father_name_en" value="{{ old('father_name_en') }}" placeholder="Father's name in English" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('father_name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Father's Name (BN)</label>
                        <input type="text" name="father_name_bn" value="{{ old('father_name_bn') }}" placeholder="পিতার নাম বাংলায়" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mother's Name (EN) *</label>
                        <input type="text" name="mother_name_en" value="{{ old('mother_name_en') }}" placeholder="Mother's name in English" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('mother_name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mother's Name (BN)</label>
                        <input type="text" name="mother_name_bn" value="{{ old('mother_name_bn') }}" placeholder="মাতার নাম বাংলায়" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile *</label>
                        <input type="text" name="contact" value="{{ old('contact') }}" placeholder="01XXXXXXXXX" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('contact') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth *</label>
                        <input type="date" name="dob" value="{{ old('dob') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('dob') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-3 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender *</label>
                        <select name="gender" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                            <option value="">— Select —</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nationality</label>
                        <select name="nationality" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="Bangladeshi" {{ old('nationality', 'Bangladeshi') == 'Bangladeshi' ? 'selected' : '' }}>Bangladeshi</option>
                            <option value="Other" {{ old('nationality') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Religion</label>
                        <select name="religion" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="">— Select —</option>
                            <option {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option {{ old('religion') == 'Hinduism' ? 'selected' : '' }}>Hinduism</option>
                            <option {{ old('religion') == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option {{ old('religion') == 'Buddhism' ? 'selected' : '' }}>Buddhism</option>
                            <option {{ old('religion') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Blood Group</label>
                        <select name="blood_group" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="">— Select —</option>
                            <option {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            <option {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Type *</label>
                        <select name="id_type" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                            <option value="">— Select —</option>
                            <option value="NID" {{ old('id_type') == 'NID' ? 'selected' : '' }}>National ID (NID)</option>
                            <option value="Birth" {{ old('id_type') == 'Birth' ? 'selected' : '' }}>Birth Registration</option>
                        </select>
                        @error('id_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Number *</label>
                        <input type="text" name="id_no" value="{{ old('id_no') }}" placeholder="Enter ID number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                        @error('id_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Occupation</label>
                        <select name="occupation" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="">— Select —</option>
                            <option {{ old('occupation') == 'Student' ? 'selected' : '' }}>Student</option>
                            <option {{ old('occupation') == 'Service Holder' ? 'selected' : '' }}>Service Holder</option>
                            <option {{ old('occupation') == 'Business' ? 'selected' : '' }}>Business</option>
                            <option {{ old('occupation') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                            <option {{ old('occupation') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                            <option {{ old('occupation') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Guardian Mobile *</label>
                            <input type="text" name="guardian_contact" value="{{ old('guardian_contact') }}" placeholder="01XXXXXXXXX" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                            @error('guardian_contact') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Relation *</label>
                            <input type="text" name="guardian_relation" value="{{ old('guardian_relation') }}" placeholder="Father, Mother, Uncle" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4" required>
                            @error('guardian_relation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{--=============== ADDRESS ===============--}}
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Present Address
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Village/Road *</label>
                        <input type="text" name="present_village" value="{{ old('present_village') }}" placeholder="Village or road name" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        @error('present_village') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Post Office</label>
                        <input type="text" name="present_po" value="{{ old('present_po') }}" placeholder="Post office" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Upazila/Thana</label>
                        <input type="text" name="present_upazila" value="{{ old('present_upazila') }}" placeholder="Upazila or thana" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">District *</label>
                        <input type="text" name="present_district" value="{{ old('present_district') }}" placeholder="District" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        @error('present_district') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Division</label>
                        <select name="present_division" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="">— Select —</option>
                            <option {{ old('present_division') == 'Dhaka' ? 'selected' : '' }}>Dhaka</option>
                            <option {{ old('present_division') == 'Chattogram' ? 'selected' : '' }}>Chattogram</option>
                            <option {{ old('present_division') == 'Rajshahi' ? 'selected' : '' }}>Rajshahi</option>
                            <option {{ old('present_division') == 'Khulna' ? 'selected' : '' }}>Khulna</option>
                            <option {{ old('present_division') == 'Barishal' ? 'selected' : '' }}>Barishal</option>
                            <option {{ old('present_division') == 'Sylhet' ? 'selected' : '' }}>Sylhet</option>
                            <option {{ old('present_division') == 'Rangpur' ? 'selected' : '' }}>Rangpur</option>
                            <option {{ old('present_division') == 'Mymensingh' ? 'selected' : '' }}>Mymensingh</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-dashed border-gray-100">
                    <label class="inline-flex items-center gap-2 cursor-pointer" onclick="document.getElementById('same_as_present').checked = !document.getElementById('same_as_present').checked; togglePermanent()">
                        <input type="checkbox" id="same_as_present" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">Same as present address</span>
                    </label>
                </div>

                <div id="permanent_address" class="mt-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Permanent Address
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Village/Road</label>
                            <input type="text" name="perm_village" value="{{ old('perm_village') }}" placeholder="Village or road name" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Post Office</label>
                            <input type="text" name="perm_po" value="{{ old('perm_po') }}" placeholder="Post office" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Upazila/Thana</label>
                            <input type="text" name="perm_upazila" value="{{ old('perm_upazila') }}" placeholder="Upazila or thana" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">District</label>
                            <input type="text" name="perm_district" value="{{ old('perm_district') }}" placeholder="District" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Division</label>
                            <select name="perm_division" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                                <option value="">— Select —</option>
                                <option {{ old('perm_division') == 'Dhaka' ? 'selected' : '' }}>Dhaka</option>
                                <option {{ old('perm_division') == 'Chattogram' ? 'selected' : '' }}>Chattogram</option>
                                <option {{ old('perm_division') == 'Rajshahi' ? 'selected' : '' }}>Rajshahi</option>
                                <option {{ old('perm_division') == 'Khulna' ? 'selected' : '' }}>Khulna</option>
                                <option {{ old('perm_division') == 'Barishal' ? 'selected' : '' }}>Barishal</option>
                                <option {{ old('perm_division') == 'Sylhet' ? 'selected' : '' }}>Sylhet</option>
                                <option {{ old('perm_division') == 'Rangpur' ? 'selected' : '' }}>Rangpur</option>
                                <option {{ old('perm_division') == 'Mymensingh' ? 'selected' : '' }}>Mymensingh</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{--=============== EDUCATION ===============--}}
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                    </svg>
                    Educational Qualification
                </h3>
                <div class="grid grid-cols-5 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Degree</label>
                        <select name="edu_degree" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                            <option value="">— Select —</option>
                            <option {{ old('edu_degree') == 'JSC/JDC' ? 'selected' : '' }}>JSC/JDC</option>
                            <option {{ old('edu_degree') == 'SSC/Dakhil' ? 'selected' : '' }}>SSC/Dakhil</option>
                            <option {{ old('edu_degree') == 'HSC/Alim' ? 'selected' : '' }}>HSC/Alim</option>
                            <option {{ old('edu_degree') == 'Honours' ? 'selected' : '' }}>Honours</option>
                            <option {{ old('edu_degree') == 'Masters' ? 'selected' : '' }}>Masters</option>
                            <option {{ old('edu_degree') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            <option {{ old('edu_degree') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Institute</label>
                        <input type="text" name="edu_institute" value="{{ old('edu_institute') }}" placeholder="Institute name" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Passing Year</label>
                        <input type="number" name="edu_year" value="{{ old('edu_year') }}" placeholder="YYYY" min="1950" max="{{ date('Y') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CGPA/GPA</label>
                        <input type="text" name="edu_cgpa" value="{{ old('edu_cgpa') }}" placeholder="e.g. 4.50" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                        <input type="text" name="edu_address" value="{{ old('edu_address') }}" placeholder="Institute address" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 text-sm py-3 px-4">
                    </div>
                </div>
            </div>

            {{--=============== DOCUMENTS ===============--}}
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Documents
                </h3>
                <p class="text-xs text-gray-400 mb-3">Upload your photo, signature and ID. You can drag to adjust the crop area.</p>
                <div class="grid grid-cols-3 gap-4">
                    {{-- Photo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Passport Photo *</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-indigo-400 transition cursor-pointer" onclick="document.getElementById('photo_input').click()">
                            <img id="photo_preview" class="w-24 h-24 object-cover rounded-lg mx-auto mb-2 hidden" src="" alt="Photo preview">
                            <div id="photo_placeholder" class="w-24 h-24 bg-gray-50 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500">300 × 300 px</p>
                        </div>
                        <input type="file" id="photo_input" accept="image/*" class="hidden">
                        <input type="hidden" id="photo_base64" name="photo_base64" value="">
                        @error('photo_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Signature --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Signature *</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-indigo-400 transition cursor-pointer" onclick="document.getElementById('signature_input').click()">
                            <img id="signature_preview" class="w-full h-12 object-contain rounded-lg mx-auto mb-2 hidden" src="" alt="Signature preview">
                            <div id="signature_placeholder" class="w-full h-12 bg-gray-50 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500">300 × 80 px</p>
                        </div>
                        <input type="file" id="signature_input" accept="image/*" class="hidden">
                        <input type="hidden" id="signature_base64" name="signature_base64" value="">
                        @error('signature_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- NID --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">NID/Birth Cert.</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-indigo-400 transition cursor-pointer" onclick="document.getElementById('nid_input').click()">
                            <img id="nid_preview" class="w-full h-20 object-contain rounded-lg mx-auto mb-2 hidden" src="" alt="NID preview">
                            <div id="nid_placeholder" class="w-full h-20 bg-gray-50 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500">ID card scan</p>
                        </div>
                        <input type="file" id="nid_input" name="nid_file" accept="image/*" class="hidden" onchange="document.getElementById('nid_preview').src=URL.createObjectURL(this.files[0]);document.getElementById('nid_preview').classList.remove('hidden');document.getElementById('nid_placeholder').classList.add('hidden')">
                        @error('nid_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Errors --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
                            <ul class="text-sm text-red-600 list-disc list-inside mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 px-6 rounded-xl transition duration-150 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Submit Application
            </button>
        </form>
    </div>
@endif

<script>
function togglePermanent() {
    const checked = document.getElementById('same_as_present').checked;
    document.getElementById('permanent_address').style.display = checked ? 'none' : 'block';
}
document.addEventListener('DOMContentLoaded', togglePermanent);
</script>

@endsection
