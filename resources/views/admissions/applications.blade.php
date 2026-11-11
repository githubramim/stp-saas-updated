<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Applications') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($students->isEmpty())
                        <p class="text-gray-500 text-center py-8">No applications yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm datatable" data-dt-page-length="20">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Reference</th>
                                        <th class="text-left p-3 font-medium">Name</th>
                                        <th class="text-left p-3 font-medium">Course</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-left p-3 font-medium">Status</th>
                                        <th class="text-left p-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 font-mono text-xs">{{ $student->reference_no }}</td>
                                            <td class="p-3">{{ $student->name_en }}</td>
                                            <td class="p-3">{{ $student->course?->name ?? '—' }}</td>
                                            <td class="p-3">{{ $student->batch?->name ?? '—' }}</td>
                                            <td class="p-3">
                                                <span class="px-2 py-1 text-xs rounded
                                                    @if($student->status === 'admitted') bg-green-100 text-green-700
                                                    @elseif($student->status === 'rejected') bg-red-100 text-red-700
                                                    @else bg-yellow-100 text-yellow-700
                                                    @endif">{{ ucfirst($student->status) }}</span>
                                            </td>
                                            <td class="p-3">
                                                <a href="{{ route('admissions.show', $student) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition-colors">View</a>
                                                <a href="{{ route('admissions.print', $student) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded hover:bg-indigo-100 transition-colors">Print</a>
                                                @if($student->status === 'applied')
                                                    <form action="{{ route('admissions.approve', $student) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 transition-colors">Admit</button>
                                                    </form>
                                                    <form action="{{ route('admissions.reject', $student) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition-colors">Reject</button>
                                                    </form>
                                                @endif
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
