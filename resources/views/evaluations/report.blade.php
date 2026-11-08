<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluation Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Trainer</label>
                            <select name="trainer_id" onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Trainers</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}" {{ $selectedTrainerId == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if($ratingsByTrainer->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    @foreach($ratingsByTrainer as $item)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h4 class="font-semibold">{{ $item['trainer']?->name ?? 'Unknown' }}</h4>
                            <div class="text-2xl font-bold text-indigo-600 mt-2">{{ $item['avg_rating'] }}</div>
                            <div class="text-yellow-500 text-lg">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= round($item['avg_rating']) ? '★' : '☆' }}
                                @endfor
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $item['count'] }} evaluation(s)</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Details</h3>
                    @if($evaluations->isEmpty())
                        <p class="text-gray-500 text-center py-8">No evaluations found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Date</th>
                                        <th class="text-left p-3 font-medium">Trainer</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-center p-3 font-medium">Rating</th>
                                        <th class="text-left p-3 font-medium">Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluations as $e)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 text-xs">{{ $e->created_at->format('d M Y') }}</td>
                                            <td class="p-3 font-medium">{{ $e->trainer?->name ?? '—' }}</td>
                                            <td class="p-3">{{ $e->batch?->name ?? '—' }}</td>
                                            <td class="p-3 text-center">
                                                <span class="text-yellow-500">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        {{ $i <= $e->rating_score ? '★' : '☆' }}
                                                    @endfor
                                                </span>
                                            </td>
                                            <td class="p-3 max-w-xs">{{ $e->review_comments ?? '—' }}</td>
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
