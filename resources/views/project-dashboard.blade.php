<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">&larr; Master Dashboard</a>
            <span class="text-gray-300">|</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->name }}</h2>
        </div>
    </x-slot>

    @php
        $gradients = [
            'from-violet-600 to-indigo-700',
            'from-emerald-500 to-teal-700',
            'from-rose-500 to-pink-700',
            'from-amber-500 to-orange-700',
            'from-cyan-500 to-blue-700',
            'from-fuchsia-500 to-purple-700',
        ];
        $g = $gradients[crc32($project->name) % count($gradients)];
    @endphp

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Project Header --}}
            <div class="bg-gradient-to-br {{ $g }} rounded-2xl shadow-lg p-6 mb-8 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $project->name }} Dashboard</h1>
                        @if($project->code)
                            <span class="text-sm bg-white/20 px-3 py-1 rounded-full mt-1 inline-block">{{ $project->code }}</span>
                        @endif
                        @if($project->description)
                            <p class="text-white/80 text-sm mt-2">{{ $project->description }}</p>
                        @endif
                    </div>
                    <span class="text-xs px-3 py-1.5 rounded-full {{ $project->status === 'active' ? 'bg-green-400/30 text-green-100' : 'bg-red-400/30 text-red-100' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 xl:grid-cols-5">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Courses</div>
                            <div class="text-lg font-bold text-gray-800">{{ $courseCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Batches</div>
                            <div class="text-lg font-bold text-gray-800">{{ $batchCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Applied</div>
                            <div class="text-lg font-bold text-gray-800">{{ $appliedCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Admitted</div>
                            <div class="text-lg font-bold text-gray-800">{{ $admittedCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Total Students</div>
                            <div class="text-lg font-bold text-gray-800">{{ $studentCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Action Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <form action="{{ route('projects.switch', $project) }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ route('admissions.applications') }}">
                    <button type="submit" class="w-full flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-300 transition group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-800 text-sm">Applications</div>
                            <div class="text-xs text-gray-400">{{ $appliedCount }} pending</div>
                        </div>
                    </button>
                </form>
                <form action="{{ route('projects.switch', $project) }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ route('admissions.students') }}">
                    <button type="submit" class="w-full flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-rose-300 transition group">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-800 text-sm">Admitted</div>
                            <div class="text-xs text-gray-400">{{ $admittedCount }} students</div>
                        </div>
                    </button>
                </form>
            </div>

            {{-- Inline Courses Management --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8" id="courses">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        Courses ({{ $courseCount }})
                    </h3>
                    <button type="button" onclick="document.getElementById('addCourseForm').classList.toggle('hidden')" class="btn btn-outline-emerald text-xs">+ Add Course</button>
                </div>
                <div id="addCourseForm" class="hidden p-5 border-b border-gray-100 bg-emerald-50">
                    <form action="{{ route('courses.inline-store', $project) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name (EN)</label>
                            <input type="text" name="name" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name (BN)</label>
                            <input type="text" name="name_bn" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                            <input type="text" name="duration" placeholder="e.g. 6 months" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fee</label>
                            <input type="number" name="fee" step="0.01" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Seat Capacity</label>
                            <input type="number" name="seat_capacity" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn btn-solid-emerald text-xs">Save Course</button>
                        </div>
                    </form>
                </div>
                @if($courses->isEmpty())
                    <div class="p-5 text-center text-gray-400 text-sm">No courses yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="text-left p-3 font-medium">Name</th>
                                    <th class="text-left p-3 font-medium">Name (BN)</th>
                                    <th class="text-left p-3 font-medium">Duration</th>
                                    <th class="text-left p-3 font-medium">Fee</th>
                                    <th class="text-left p-3 font-medium">Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $c)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-3 font-medium">{{ $c->name }}</td>
                                        <td class="p-3 text-gray-500">{{ $c->name_bn ?? '—' }}</td>
                                        <td class="p-3 text-gray-500">{{ $c->duration ?? '—' }}</td>
                                        <td class="p-3 text-gray-500">{{ number_format($c->fee, 0) }}</td>
                                        <td class="p-3 text-gray-500">{{ $c->seat_capacity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Inline Batches Management --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8" id="batches">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        Batches ({{ $batchCount }})
                    </h3>
                    <button type="button" onclick="document.getElementById('addBatchForm').classList.toggle('hidden')" class="btn btn-outline-amber text-xs">+ Add Batch</button>
                </div>
                <div id="addBatchForm" class="hidden p-5 border-b border-gray-100 bg-amber-50">
                    <form action="{{ route('batches.inline-store', $project) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Course</label>
                            <select name="course_id" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Select Course</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name (EN)</label>
                            <input type="text" name="name" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name (BN)</label>
                            <input type="text" name="name_bn" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Shift</label>
                            <input type="text" name="shift" placeholder="e.g. Morning" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Seat Capacity</label>
                            <input type="number" name="seat_capacity" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn btn-solid-amber text-xs">Save Batch</button>
                        </div>
                    </form>
                </div>
                @if($batches->isEmpty())
                    <div class="p-5 text-center text-gray-400 text-sm">No batches yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="text-left p-3 font-medium">Name</th>
                                    <th class="text-left p-3 font-medium">Course</th>
                                    <th class="text-left p-3 font-medium">Shift</th>
                                    <th class="text-left p-3 font-medium">Period</th>
                                    <th class="text-left p-3 font-medium">Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $b)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-3 font-medium">{{ $b->name }}</td>
                                        <td class="p-3 text-gray-500">{{ $b->course?->name ?? '—' }}</td>
                                        <td class="p-3 text-gray-500">{{ $b->shift ?? '—' }}</td>
                                        <td class="p-3 text-gray-500">
                                            @if($b->start_date)
                                                {{ $b->start_date->format('d M') }} - {{ $b->end_date?->format('d M Y') ?? '?' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="p-3 text-gray-500">{{ $b->seat_capacity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- All Students Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        All Students ({{ $studentCount }})
                    </h3>
                    <div class="flex gap-2">
                        <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-700">{{ $appliedCount }} Applied</span>
                        <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">{{ $admittedCount }} Admitted</span>
                    </div>
                </div>
                @if($students->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No students in this project yet.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="text-left p-3 font-medium">Name</th>
                                    <th class="text-left p-3 font-medium">Course</th>
                                    <th class="text-left p-3 font-medium">Batch</th>
                                    <th class="text-left p-3 font-medium">Status</th>
                                    <th class="text-left p-3 font-medium">Date</th>
                                    <th class="text-left p-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-3 font-medium">{{ $s->name_en }}</td>
                                        <td class="p-3 text-gray-500">{{ $s->course?->name ?? '—' }}</td>
                                        <td class="p-3 text-gray-500">{{ $s->batch?->name ?? '—' }}</td>
                                        <td class="p-3">
                                            <span class="px-2 py-1 text-xs rounded
                                                @if($s->status === 'admitted') bg-green-100 text-green-700
                                                @elseif($s->status === 'rejected') bg-red-100 text-red-700
                                                @else bg-yellow-100 text-yellow-700
                                                @endif">{{ ucfirst($s->status) }}</span>
                                        </td>
                                        <td class="p-3 text-gray-400 text-xs">{{ $s->created_at->format('d M Y') }}</td>
                                        <td class="p-3">
                                            <div class="flex gap-1.5">
                                                <a href="{{ route('admissions.show', $s) }}" class="btn btn-outline-sky" target="_blank">View</a>
                                                <a href="{{ route('admissions.print', $s) }}" class="btn btn-outline-indigo" target="_blank">Print</a>
                                                @if($s->status === 'applied')
                                                    <form action="{{ route('admissions.approve', $s) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-solid-emerald">Admit</button>
                                                    </form>
                                                    <form action="{{ route('admissions.reject', $s) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-red">Reject</button>
                                                    </form>
                                                @endif
                                                @if($s->status === 'admitted')
                                                    <form action="{{ route('admissions.change-batch', $s) }}" method="POST" class="inline flex gap-1">
                                                        @csrf
                                                        <select name="batch_id" class="text-xs rounded border-gray-300 w-24" onchange="this.form.submit()">
                                                            <option value="">Batch</option>
                                                            @foreach($batches as $b)
                                                                <option value="{{ $b->id }}" {{ $s->batch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
