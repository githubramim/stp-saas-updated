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
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createEvaluationModal" class="btn btn-solid-indigo">+ New Evaluation</button>
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
    {{-- Create Evaluation Modal --}}
    <div class="modal fade" id="createEvaluationModal" tabindex="-1" aria-labelledby="createEvaluationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createEvaluationModalLabel">
                        <i class="bi bi-star text-yellow-400"></i> {{ __('New Evaluation') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('evaluations.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_eval_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch') }} *</label>
                                <select id="create_eval_batch" name="batch_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Batch') }} —</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}" @selected(old('batch_id') == $b->id)>
                                            {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_eval_trainer" class="form-label fw-semibold text-slate-700 small">{{ __('Trainer') }} *</label>
                                <select id="create_eval_trainer" name="trainer_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Trainer') }} —</option>
                                    @foreach($trainers as $t)
                                        <option value="{{ $t->id }}" @selected(old('trainer_id') == $t->id)>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Rating') }}</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="d-inline-flex align-items-center gap-1 rounded-3 border px-3 py-2 small" style="cursor:pointer;">
                                            <input type="radio" name="rating_score" value="{{ $i }}" @checked(old('rating_score', 5) == $i)>
                                            {{ $i }} <i class="bi bi-star-fill text-warning"></i>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="create_eval_comments" class="form-label fw-semibold text-slate-700 small">{{ __('Comments (Optional)') }}</label>
                                <textarea id="create_eval_comments" name="review_comments" rows="3" class="form-control rounded-3">{{ old('review_comments') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="d-inline-flex align-items-center gap-2 small text-slate-700">
                                    <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous', true)) class="rounded border-slate-300 text-indigo-600">
                                    {{ __('Submit anonymously') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Submit Evaluation') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var modal = document.getElementById('createEvaluationModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
