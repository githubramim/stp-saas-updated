<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Trainer Performance') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Student evaluations, admin rating, attendance and assigned batches.') }}</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('hr.partials.nav')

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Trainer') }}</th>
                                <th>{{ __('Student Rating') }}</th>
                                <th>{{ __('Admin Rating') }}</th>
                                <th>{{ __('Overall') }}</th>
                                <th>{{ __('Attendance') }}</th>
                                <th>{{ __('Batches') }}</th>
                                <th class="pe-4">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainers as $row)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $row['user']->name }}</div>
                                        <div class="text-muted small">{{ $row['profile']->employee_code ?: '—' }}</div>
                                    </td>
                                    <td>
                                        {{ $row['student_rating'] > 0 ? number_format($row['student_rating'], 2).' / 5' : '—' }}
                                        <div class="text-muted small">{{ $row['rating_count'] }} {{ __('reviews') }}</div>
                                    </td>
                                    <td>{{ $row['admin_rating'] ? number_format($row['admin_rating'], 1).' / 5' : '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $row['score'] >= 4.5 ? 'success' : ($row['score'] >= 3.5 ? 'primary' : ($row['score'] >= 2.5 ? 'warning' : 'danger')) }}">
                                            {{ $row['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['attendance_rate'] !== null ? $row['attendance_rate'].'%' : '—' }}</td>
                                    <td>{{ $row['batches'] }}</td>
                                    <td class="pe-4 text-muted small">{{ $row['profile']->performance_notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-6 text-center text-muted">{{ __('No trainers in this branch.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-400">{{ __('Admin rating takes priority over student evaluations when both are available.') }}</p>
        </div>
    </div>
</x-app-layout>
