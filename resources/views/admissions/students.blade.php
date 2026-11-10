<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admitted Students') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($students->isEmpty())
                        <p class="text-gray-500 text-center py-8">No admitted students yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Name</th>
                                        <th class="text-left p-3 font-medium">Course</th>
                                        <th class="text-left p-3 font-medium">Occupation</th>
                                        <th class="text-left p-3 font-medium">Level</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-left p-3 font-medium">Contact</th>
                                        <th class="text-left p-3 font-medium">Change Batch</th>
                                        <th class="text-left p-3 font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3">{{ $student->name_en }}</td>
                                            <td class="p-3">{{ $student->course?->name ?? '—' }}</td>
                                            <td class="p-3">{{ $student->course?->tam_occupation ?? $student->occupation ?? '—' }}</td>
                                            <td class="p-3">
                                                @if($student->ntvqf_level || $student->course?->ntvqf_level)
                                                    <span class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">L{{ $student->ntvqf_level ?: $student->course->ntvqf_level }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="p-3">{{ $student->batch?->name ?? '—' }}</td>
                                            <td class="p-3">{{ $student->contact }}</td>
                                            <td class="p-3">
                                                <form action="{{ route('admissions.change-batch', $student) }}" method="POST" class="flex gap-2">
                                                    @csrf
                                                    <select name="batch_id" class="text-xs rounded border-gray-300">
                                                        @foreach($batches as $batch)
                                                            <option value="{{ $batch->id }}" {{ $student->batch_id == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900">Update</button>
                                                </form>
                                            </td>
                                            <td class="p-3">
                                                <a href="{{ route('admissions.print', $student) }}" target="_blank" rel="noopener"
                                                   class="mr-2 inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-900">
                                                    <i class="bi bi-printer"></i> Print
                                                </a>
                                                <form action="{{ route('admissions.remove-batch', $student) }}" method="POST" class="inline" onsubmit="return confirm('Remove from batch?')">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                                </form>
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
    </div>
</x-app-layout>
