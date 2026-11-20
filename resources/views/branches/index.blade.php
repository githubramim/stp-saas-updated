<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-diagram-2 text-indigo-500"></i>
                <span class="truncate">{{ __('Branch Management') }}</span>
            </h2>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" class="btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                    <i class="bi bi-plus-circle-fill"></i>
                    {{ __('New Branch') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ============ SUMMARY CARDS ============ --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <i class="bi bi-diagram-2 text-indigo-500"></i> {{ __('Total Branches') }}
                    </p>
                    <p class="mt-1.5 text-2xl font-black text-slate-800">{{ $branches->count() }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Unlimited branches per institute') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <i class="bi bi-person-badge text-cyan-500"></i> {{ __('Assigned Staff') }}
                    </p>
                    <p class="mt-1.5 text-2xl font-black text-slate-800">{{ $staff->whereNotNull('branch_id')->count() }}<span class="text-sm font-semibold text-slate-400"> / {{ $staff->count() }}</span></p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Staff with a branch') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <i class="bi bi-mortarboard text-emerald-500"></i> {{ __('Assigned Students') }}
                    </p>
                    <p class="mt-1.5 text-2xl font-black text-slate-800">{{ $students->whereNotNull('branch_id')->count() }}<span class="text-sm font-semibold text-slate-400"> / {{ $students->count() }}</span></p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Students with a branch') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <i class="bi bi-graph-up-arrow text-lime-500"></i> {{ __('Net P&L') }} ({{ $period === 'all' ? __('All time') : ($period === 'this_year' ? __('This year') : __('This month')) }})
                    </p>
                    <p class="mt-1.5 text-2xl font-black {{ $totals['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $totals['net'] >= 0 ? '৳' : '-৳' }}{{ number_format(abs($totals['net'])) }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Income ৳') }}{{ number_format($totals['income']) }} · {{ __('Expense ৳') }}{{ number_format($totals['expense']) }}</p>
                </div>
            </div>

            {{-- ============ PERIOD FILTER ============ --}}
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('Branch-wise P&L period') }}:</span>
                @foreach(['this_month' => __('This month'), 'this_year' => __('This year'), 'all' => __('All time')] as $key => $label)
                    <a href="{{ route('ui.set', ['branches.index', 'period:'.$key]) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $period === $key ? 'bg-indigo-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- ============ BRANCH TABLE ============ --}}
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ __('Branches') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $branches->count() }} {{ __('total') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50/60">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-3 sm:px-5">{{ __('Branch') }}</th>
                                <th class="px-3 py-3">{{ __('Contact') }}</th>
                                <th class="px-3 py-3 text-center">{{ __('Staff') }}</th>
                                <th class="px-3 py-3 text-center">{{ __('Students') }}</th>
                                <th class="px-3 py-3 text-right">{{ __('Income') }}</th>
                                <th class="px-3 py-3 text-right">{{ __('Expense') }}</th>
                                <th class="px-3 py-3 text-right">{{ __('Net') }}</th>
                                <th class="px-4 py-3 sm:px-5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($branches as $branch)
                                @php($row = $pl[$branch->id])
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3 sm:px-5">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-xs font-black text-white">
                                                {{ strtoupper(substr($branch->name, 0, 2)) }}
                                            </span>
                                            <div>
                                                <p class="font-bold text-slate-800">{{ $branch->name }}</p>
                                                <div class="flex items-center gap-1.5">
                                                    @if($branch->code)
                                                        <span class="text-[11px] font-semibold text-slate-400">{{ $branch->code }}</span>
                                                    @endif
                                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $branch->isActive() ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                                        {{ $branch->isActive() ? __('Active') : __('Inactive') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-slate-500">
                                        @if($branch->address)<span class="block">{{ $branch->address }}</span>@endif
                                        @if($branch->phone)<span class="block">{{ $branch->phone }}</span>@endif
                                        @if(!$branch->address && !$branch->phone)—@endif
                                    </td>
                                    <td class="px-3 py-3 text-center font-semibold text-slate-600">{{ $branch->users_count }}</td>
                                    <td class="px-3 py-3 text-center font-semibold text-slate-600">{{ $branch->students_count }}</td>
                                    <td class="px-3 py-3 text-right font-semibold text-emerald-600">৳{{ number_format($row['income']) }}</td>
                                    <td class="px-3 py-3 text-right font-semibold text-rose-500">৳{{ number_format($row['expense']) }}</td>
                                    <td class="px-3 py-3 text-right font-black {{ $row['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $row['net'] >= 0 ? '৳' : '-৳' }}{{ number_format(abs($row['net'])) }}
                                    </td>
                                    <td class="px-4 py-3 sm:px-5">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                                                    data-bs-toggle="modal" data-bs-target="#assignStaffModal" data-branch-id="{{ $branch->id }}"
                                                    title="{{ __('Assign staff') }}">
                                                <i class="bi bi-person-badge"></i> {{ __('Staff') }}
                                            </button>
                                            <button type="button"
                                                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                                    data-bs-toggle="modal" data-bs-target="#assignStudentsModal" data-branch-id="{{ $branch->id }}"
                                                    title="{{ __('Assign students') }}">
                                                <i class="bi bi-mortarboard"></i> {{ __('Students') }}
                                            </button>
                                            <button type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600"
                                                    data-bs-toggle="modal" data-bs-target="#editBranchModal{{ $branch->id }}"
                                                    title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirmDelete(event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                                        title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-16 text-center text-slate-500">
                                        <i class="bi bi-diagram-2 mb-3 block text-4xl text-slate-300"></i>
                                        <p class="text-sm font-medium">{{ __('No branches yet. Create your first branch.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                            @if($unassigned['income'] > 0 || $unassigned['expense'] > 0)
                                <tr class="bg-amber-50/40">
                                    <td class="px-4 py-3 sm:px-5">
                                        <p class="font-bold text-amber-700">{{ __('Unassigned') }}</p>
                                        <p class="text-[11px] text-amber-600">{{ __('Transactions without a branch') }}</p>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-right font-semibold text-emerald-600">৳{{ number_format($unassigned['income']) }}</td>
                                    <td class="px-3 py-3 text-right font-semibold text-rose-500">৳{{ number_format($unassigned['expense']) }}</td>
                                    <td class="px-3 py-3 text-right font-black {{ $unassigned['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $unassigned['net'] >= 0 ? '৳' : '-৳' }}{{ number_format(abs($unassigned['net'])) }}
                                    </td>
                                    <td class="px-4 py-3 sm:px-5"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                <i class="bi bi-info-circle"></i>
                {{ __('Institutes without a branch automatically get "Branch 1". New income, expense and admitted students are tagged to the branch selected in the top bar.') }}
            </p>
        </div>
    </div>

    {{-- ============ CREATE MODAL ============ --}}
    <div class="modal fade" id="createBranchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2">
                        <i class="bi bi-plus-circle text-indigo-400"></i> {{ __('New Branch') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('branches.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Branch Name') }} *</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('e.g. Dhaka, Chattogram, Sylhet') }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Branch Code') }}</label>
                                <input type="text" name="code" value="{{ old('code') }}" placeholder="{{ __('e.g. DHK-01') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Address') }}</label>
                                <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700 small">{{ __('Status') }}</label>
                                <select name="status" class="form-select rounded-3">
                                    <option value="active" selected>{{ __('Active') }}</option>
                                    <option value="inactive">{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn-outline-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-solid-indigo"><i class="bi bi-check-circle-fill"></i> {{ __('Create Branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============ EDIT MODALS ============ --}}
    @foreach($branches as $branch)
        <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-slate-900 text-white rounded-top-4">
                        <h5 class="modal-title fw-bold text-white flex items-center gap-2">
                            <i class="bi bi-pencil-square text-amber-400"></i> {{ __('Edit Branch') }} — {{ $branch->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <form method="POST" action="{{ route('branches.update', $branch) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            @include('partials.modal-errors')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-slate-700 small">{{ __('Branch Name') }} *</label>
                                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-slate-700 small">{{ __('Branch Code') }}</label>
                                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-slate-700 small">{{ __('Phone') }}</label>
                                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="form-control rounded-3">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-slate-700 small">{{ __('Address') }}</label>
                                    <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address', $branch->address) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-slate-700 small">{{ __('Status') }}</label>
                                    <select name="status" class="form-select rounded-3">
                                        <option value="active" @selected($branch->status === 'active')>{{ __('Active') }}</option>
                                        <option value="inactive" @selected($branch->status === 'inactive')>{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn-outline-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn-solid-indigo"><i class="bi bi-check-circle-fill"></i> {{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- ============ ASSIGN STAFF MODAL ============ --}}
    <div class="modal fade" id="assignStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2">
                        <i class="bi bi-person-badge text-cyan-400"></i> {{ __('Assign Staff to Branch') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('branches.assign') }}">
                    @csrf
                    <input type="hidden" name="type" value="staff">
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 small">{{ __('Target Branch') }} *</label>
                            <select name="branch_id" class="form-select rounded-3" required>
                                @foreach($branches as $branchOption)
                                    <option value="{{ $branchOption->id }}">{{ $branchOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-xs text-slate-400 mb-2">{{ __('Select the staff members to move into this branch. Current branch is shown next to each name.') }}</p>
                        <div class="border rounded-3 overflow-hidden" style="max-height: 45vh; overflow-y: auto;">
                            @foreach($staff as $staffMember)
                                <label class="d-flex align-items-center gap-2 border-bottom py-2 px-3 mb-0" style="cursor: pointer;">
                                    <input type="checkbox" name="people[]" value="{{ $staffMember->id }}" class="form-check-input mt-0">
                                    <span class="fw-semibold text-slate-700 small">{{ $staffMember->name }}</span>
                                    <span class="badge bg-light text-dark border ms-1">{{ $staffMember->role ?? __('Staff') }}</span>
                                    <span class="ms-auto small text-slate-400">
                                        {{ $branches->firstWhere('id', $staffMember->branch_id)?->name ?? __('Unassigned') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn-outline-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-solid-indigo"><i class="bi bi-people-fill"></i> {{ __('Assign to Branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============ ASSIGN STUDENTS MODAL ============ --}}
    <div class="modal fade" id="assignStudentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2">
                        <i class="bi bi-mortarboard text-emerald-400"></i> {{ __('Assign Students to Branch') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('branches.assign') }}">
                    @csrf
                    <input type="hidden" name="type" value="students">
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 small">{{ __('Target Branch') }} *</label>
                            <select name="branch_id" class="form-select rounded-3" required>
                                @foreach($branches as $branchOption)
                                    <option value="{{ $branchOption->id }}">{{ $branchOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" id="assignStudentSearch" class="form-control form-control-sm rounded-3" placeholder="{{ __('Search students…') }}">
                        </div>
                        <p class="text-xs text-slate-400 mb-2">{{ __('Select the students to move into this branch. Current branch is shown next to each name.') }}</p>
                        <div class="border rounded-3 overflow-hidden" style="max-height: 45vh; overflow-y: auto;">
                            @foreach($students as $student)
                                <label class="assign-student-row d-flex align-items-center gap-2 border-bottom py-2 px-3 mb-0" style="cursor: pointer;" data-name="{{ strtolower($student->name_en . ' ' . ($student->reference_no ?? '')) }}">
                                    <input type="checkbox" name="people[]" value="{{ $student->id }}" class="form-check-input mt-0">
                                    <span class="fw-semibold text-slate-700 small">{{ $student->name_en }}</span>
                                    @if($student->reference_no)
                                        <span class="badge bg-light text-dark border ms-1">{{ $student->reference_no }}</span>
                                    @endif
                                    <span class="ms-auto small text-slate-400">
                                        {{ $branches->firstWhere('id', $student->branch_id)?->name ?? __('Unassigned') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn-outline-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-solid-indigo"><i class="bi bi-people-fill"></i> {{ __('Assign to Branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Pre-select the target branch when a branch's assign button opens a modal.
                document.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    if (!trigger) return;
                    var branchId = trigger.getAttribute('data-branch-id');
                    if (!branchId) return;
                    var select = event.target.querySelector('select[name="branch_id"]');
                    if (select) select.value = branchId;
                });

                // Simple search filter for the student assignment list.
                var search = document.getElementById('assignStudentSearch');
                if (search) {
                    search.addEventListener('input', function () {
                        var term = search.value.toLowerCase();
                        document.querySelectorAll('.assign-student-row').forEach(function (row) {
                            row.style.display = (row.getAttribute('data-name') || '').indexOf(term) !== -1 ? '' : 'none';
                        });
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
