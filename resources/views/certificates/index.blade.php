<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-award text-orange-500"></i>
                <span class="truncate">{{ __('Certificates') }}</span>
            </h2>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" class="btn-outline-gray" data-bs-toggle="modal" data-bs-target="#bulkCertificateModal">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    {{ __('Bulk Upload') }}
                </button>
                <button type="button" class="btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createCertificateModal">
                    <i class="bi bi-plus-circle-fill"></i>
                    {{ __('New Certificate') }}
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

            @if(session('bulk_errors'))
                @php($bulkErrors = session('bulk_errors'))
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="flex items-center gap-1.5 font-bold">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ __('Skipped rows') }}
                    </p>
                    <ul class="mt-1.5 max-h-48 space-y-0.5 overflow-auto pl-4 text-xs font-medium list-disc">
                        @foreach($bulkErrors as $bulkError)
                            <li>{{ $bulkError }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ __('Issued Certificates') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $certificates->total() }} total</span>
                </div>

                @if($certificates->isEmpty())
                    <div class="px-4 py-16 text-center text-slate-500">
                        <i class="bi bi-award mb-3 block text-4xl text-slate-300"></i>
                        <p class="text-sm font-medium">{{ __('No certificates issued yet.') }}</p>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createCertificateModal" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ __('Issue your first certificate') }} &rarr;
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left text-sm datatable"
                               data-dt-paging="false" data-dt-searching="false" data-dt-info="false">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Title</th>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">Issued</th>
                                    <th class="px-4 py-3">Code</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($certificates as $c)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $c->certificate_no }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $c->title }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $c->student?->name_en ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $c->issued_date ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-500">{{ $c->verification_code }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($certificates, 'links'))
                        <div class="border-t border-slate-100 px-4 py-3">
                            {{ $certificates->links() }}
                        </div>
                    @endif
                @endif
            </div>
    {{-- Create Certificate Modal --}}
    <div class="modal fade" id="createCertificateModal" tabindex="-1" aria-labelledby="createCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createCertificateModalLabel">
                        <i class="bi bi-award text-orange-400"></i> {{ __('New Certificate') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('certificates.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create_certificate_title" class="form-label fw-semibold text-slate-700 small">{{ __('Certificate Title') }} *</label>
                                <input id="create_certificate_title" type="text" name="title" value="{{ old('title', 'Certificate of Completion') }}" required
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_certificate_student" class="form-label fw-semibold text-slate-700 small">{{ __('Student') }}</label>
                                <select id="create_certificate_student" name="student_id" class="form-select rounded-3">
                                    <option value="">{{ __('— Select Student —') }}</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                            {{ $student->name_en }} @if($student->reference_no) ({{ $student->reference_no }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_certificate_exam" class="form-label fw-semibold text-slate-700 small">{{ __('Exam') }}</label>
                                <select id="create_certificate_exam" name="exam_id" class="form-select rounded-3">
                                    <option value="">{{ __('— Select Exam —') }}</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" @selected(old('exam_id') == $exam->id)>{{ $exam->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_certificate_date" class="form-label fw-semibold text-slate-700 small">{{ __('Issued Date') }}</label>
                                <input id="create_certificate_date" type="date" name="issued_date" value="{{ old('issued_date', now()->toDateString()) }}"
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <div class="flex items-start gap-2 rounded-3 border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                                    <i class="bi bi-info-circle mt-0.5"></i>
                                    <p>{{ __('A unique certificate number and verification code are generated automatically when you save.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Create Certificate') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Upload Modal --}}
    <div class="modal fade" id="bulkCertificateModal" tabindex="-1" aria-labelledby="bulkCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="bulkCertificateModalLabel">
                        <i class="bi bi-file-earmark-arrow-up text-orange-400"></i> {{ __('Bulk Upload Certificates') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('certificates.bulk') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')

                        <a href="{{ route('certificates.bulk-template') }}"
                           class="flex items-center gap-3 rounded-3 border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-100">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm">
                                <i class="bi bi-file-earmark-excel fs-5"></i>
                            </span>
                            <span class="flex-1">
                                {{ __('Download the demo Excel file') }}
                                <span class="block text-xs font-medium text-indigo-500">{{ __('Pre-filled with your students so you can try the upload right away.') }}</span>
                            </span>
                            <i class="bi bi-download"></i>
                        </a>

                        <div class="mt-3">
                            <label for="bulk_certificate_file" class="form-label fw-semibold text-slate-700 small">{{ __('Excel or CSV file') }} *</label>
                            <input id="bulk_certificate_file" type="file" name="file" accept=".xlsx,.csv,.txt" required
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700">
                            <p class="mt-1 text-xs text-slate-400">{{ __('XLSX or CSV, up to 5 MB and 1,000 rows.') }}</p>
                        </div>

                        <div class="mt-3 rounded-3 border border-slate-200 bg-slate-50 px-3 py-3 text-xs text-slate-600">
                            <p class="fw-bold text-slate-700">{{ __('Accepted columns (header row first)') }}</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <code class="rounded bg-white px-1.5 py-0.5 text-[11px] shadow-sm">student_reference</code>
                                <code class="rounded bg-white px-1.5 py-0.5 text-[11px] shadow-sm">student_name</code>
                                <code class="rounded bg-white px-1.5 py-0.5 text-[11px] shadow-sm">title *</code>
                                <code class="rounded bg-white px-1.5 py-0.5 text-[11px] shadow-sm">exam_title</code>
                                <code class="rounded bg-white px-1.5 py-0.5 text-[11px] shadow-sm">issued_date</code>
                            </div>
                            <ul class="mt-2 list-disc pl-4 space-y-0.5 text-[11px] text-slate-500">
                                <li>{{ __('Students are matched by reference number, or by name / ID if the reference column is empty.') }}</li>
                                <li>{{ __('Exam and date columns are optional. Dates can be 2026-01-31 or 31/01/2026.') }}</li>
                                <li>{{ __('Certificate numbers and verification codes are generated automatically.') }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 font-bold">
                            <i class="bi bi-upload me-1"></i> {{ __('Upload & Import') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(request('bulk') || $errors->has('file'))
                    var bulkModal = document.getElementById('bulkCertificateModal');
                    if (bulkModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(bulkModal).show();
                @elseif($errors->any() || request('create'))
                    var modal = document.getElementById('createCertificateModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
