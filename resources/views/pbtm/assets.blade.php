<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-box-seam me-1 text-cyan-500"></i>
                    {{ __('Asset Distribution & Return Tracking') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('Track project equipment, trainee hand-overs and returns for audit-ready reporting.') }}</p>
            </div>
            <button type="button" class="btn btn-primary rounded-3 font-bold" data-bs-toggle="modal" data-bs-target="#createAssetModal">
                <i class="bi bi-plus-circle me-1"></i> {{ __('New Asset') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Total Units') }}</div>
                    <div class="mt-1 text-2xl font-black text-slate-800">{{ number_format($totals['total']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Distributed') }}</div>
                    <div class="mt-1 text-2xl font-black text-amber-600">{{ number_format($totals['distributed']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Returned') }}</div>
                    <div class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($totals['returned']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Recorded Value') }}</div>
                    <div class="mt-1 text-2xl font-black text-slate-800">৳{{ number_format($totals['value'], 0) }}</div>
                </div>
            </div>

            <div class="mb-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div>
                        <label for="asset_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="asset_status" name="status" class="form-select form-select-sm rounded-xl">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-xl font-bold"><i class="bi bi-funnel me-1"></i> {{ __('Filter') }}</button>
                    <a href="{{ route('pbtm.assets', array_filter(['project_id' => $project?->id])) }}" class="btn btn-light btn-sm rounded-xl border">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                @if($assets->isEmpty())
                    <div class="p-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                            <i class="bi bi-box-seam text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('No assets recorded') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Record project equipment and hand it over to trainees to build a complete distribution trail.') }}</p>
                        <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createAssetModal">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('New Asset') }}
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 980px;" data-dt-page-length="25">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Asset') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th class="text-center">{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Unit Cost') }}</th>
                                    <th>{{ __('Distribution') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-slate-800">{{ $asset->name }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $asset->category ?: __('Uncategorised') }}
                                                @if($asset->asset_code) · {{ $asset->asset_code }} @endif
                                            </div>
                                        </td>
                                        <td class="text-slate-600">{{ $asset->project?->name ?? '—' }}</td>
                                        <td class="text-slate-600">{{ $asset->student?->name_en ?? '—' }}</td>
                                        <td class="text-center font-bold">{{ $asset->quantity }}</td>
                                        <td class="text-end">৳{{ number_format((float) $asset->unit_cost, 2) }}</td>
                                        <td>
                                            <div class="text-slate-700">{{ $asset->distribution_date?->format('d M Y') ?? '—' }}</div>
                                            @if($asset->return_date)
                                                <div class="text-[11px] text-emerald-600">{{ __('Returned') }} {{ $asset->return_date->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $assetBadge = match ($asset->status) {
                                                    'distributed' => 'bg-warning text-dark',
                                                    'returned' => 'bg-success',
                                                    'damaged', 'lost' => 'bg-danger',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $assetBadge }}">{{ $statuses[$asset->status] ?? ucfirst($asset->status) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @if($asset->status === 'distributed')
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 asset-return-btn"
                                                            data-bs-toggle="modal" data-bs-target="#returnAssetModal"
                                                            data-asset-id="{{ $asset->id }}" data-asset-name="{{ $asset->name }}">
                                                        {{ __('Return') }}
                                                    </button>
                                                @endif
                                                <form method="POST" action="{{ route('pbtm.assets.destroy', $asset) }}" onsubmit="return confirm('Delete this asset record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">{{ __('Delete') }}</button>
                                                </form>
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

    <div class="modal fade" id="createAssetModal" tabindex="-1" aria-labelledby="createAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createAssetModalLabel">
                        <i class="bi bi-box-seam text-cyan-400"></i> {{ __('New Project Asset') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('pbtm.assets.store') }}">
                    @csrf
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="asset_name" class="form-label fw-semibold text-slate-700 small">{{ __('Asset Name') }} *</label>
                                <input id="asset_name" type="text" name="name" value="{{ old('name') }}" class="form-control rounded-3" required placeholder="{{ __('e.g. Sewing Machine') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="asset_code" class="form-label fw-semibold text-slate-700 small">{{ __('Asset Code') }}</label>
                                <input id="asset_code" type="text" name="asset_code" value="{{ old('asset_code') }}" class="form-control rounded-3 font-monospace" placeholder="{{ __('AST-001') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="asset_category" class="form-label fw-semibold text-slate-700 small">{{ __('Category') }}</label>
                                <input id="asset_category" type="text" name="category" value="{{ old('category') }}" class="form-control rounded-3" placeholder="{{ __('Equipment, tool, kit…') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="asset_quantity" class="form-label fw-semibold text-slate-700 small">{{ __('Quantity') }} *</label>
                                <input id="asset_quantity" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-3">
                                <label for="asset_unit_cost" class="form-label fw-semibold text-slate-700 small">{{ __('Unit Cost (৳)') }}</label>
                                <input id="asset_unit_cost" type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost') }}" class="form-control rounded-3" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label for="asset_student" class="form-label fw-semibold text-slate-700 small">{{ __('Assign To Trainee') }}</label>
                                <select id="asset_student" name="student_id" class="form-select rounded-3">
                                    <option value="">{{ __('Not assigned / stock') }}</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name_en }} · {{ $student->reference_no }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="asset_status_input" class="form-label fw-semibold text-slate-700 small">{{ __('Status') }} *</label>
                                <select id="asset_status_input" name="status" class="form-select rounded-3" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', 'in_stock') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="asset_distribution_date" class="form-label fw-semibold text-slate-700 small">{{ __('Distribution Date') }}</label>
                                <input id="asset_distribution_date" type="date" name="distribution_date" value="{{ old('distribution_date') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="asset_condition" class="form-label fw-semibold text-slate-700 small">{{ __('Condition') }}</label>
                                <input id="asset_condition" type="text" name="condition" value="{{ old('condition') }}" class="form-control rounded-3" placeholder="{{ __('New, good, fair…') }}">
                            </div>
                            <div class="col-12">
                                <label for="asset_remarks" class="form-label fw-semibold text-slate-700 small">{{ __('Remarks') }}</label>
                                <textarea id="asset_remarks" name="remarks" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional notes') }}">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Asset') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="returnAssetModal" tabindex="-1" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="returnAssetModalLabel">
                        <i class="bi bi-arrow-return-left text-cyan-400"></i> {{ __('Return Asset') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" id="returnAssetForm">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="small text-slate-500 mb-3">{{ __('Returning:') }} <strong id="returnAssetName">—</strong></p>
                        <label for="return_condition" class="form-label fw-semibold text-slate-700 small">{{ __('Return Condition') }}</label>
                        <input id="return_condition" type="text" name="condition" class="form-control rounded-3" placeholder="{{ __('Good, damaged, needs repair…') }}">
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">{{ __('Confirm Return') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var modal = document.getElementById('createAssetModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif

                var returnForm = document.getElementById('returnAssetForm');
                var returnName = document.getElementById('returnAssetName');
                document.querySelectorAll('.asset-return-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var id = btn.getAttribute('data-asset-id');
                        returnForm.action = '{{ url('pbtm/assets') }}/' + id + '/return';
                        returnName.textContent = btn.getAttribute('data-asset-name');
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
