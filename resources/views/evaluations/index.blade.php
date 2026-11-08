<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trainer Evaluations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">All Evaluations</h3>
                        <a href="{{ route('evaluations.create') }}" class="btn btn-solid-indigo">+ New Evaluation</a>
                    </div>

                    @if($evaluations->isEmpty())
                        <p class="text-gray-500 text-center py-8">No evaluations yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Date</th>
                                        <th class="text-left p-3 font-medium">Trainer</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-center p-3 font-medium">Rating</th>
                                        <th class="text-left p-3 font-medium">Anonymous</th>
                                        <th class="text-left p-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluations as $evaluation)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 text-xs">{{ $evaluation->created_at->format('d M Y') }}</td>
                                            <td class="p-3 font-medium">{{ $evaluation->trainer?->name ?? '—' }}</td>
                                            <td class="p-3">{{ $evaluation->batch?->name ?? '—' }}</td>
                                            <td class="p-3 text-center">
                                                <span class="text-yellow-500">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        {{ $i <= $evaluation->rating_score ? '★' : '☆' }}
                                                    @endfor
                                                </span>
                                                <span class="text-xs text-gray-500">({{ $evaluation->rating_score }}/5)</span>
                                            </td>
                                            <td class="p-3">{{ $evaluation->is_anonymous ? 'Yes' : 'No' }}</td>
                                            <td class="p-3">
                                                <a href="{{ route('evaluations.report') }}?trainer_id={{ $evaluation->trainer_id }}" class="btn btn-outline-indigo text-xs">Report</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $evaluations->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
