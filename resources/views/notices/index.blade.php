<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-megaphone text-pink-500"></i>
                <span class="truncate">{{ __('Notices') }}</span>
            </h2>
            <button type="button" class="btn-solid-indigo shrink-0" data-bs-toggle="modal" data-bs-target="#createNoticeModal">
                <i class="bi bi-plus-circle-fill"></i>
                {{ __('New Notice') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($notices->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-16 text-center text-slate-500">
                    <i class="bi bi-megaphone mb-3 block text-4xl text-slate-300"></i>
                    <p class="text-sm font-medium">{{ __('No notices published yet.') }}</p>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createNoticeModal" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        {{ __('Publish your first notice') }} &rarr;
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($notices as $n)
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md sm:p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h3 class="flex min-w-0 items-center gap-2 text-base font-bold text-slate-800">
                                    @if($n->is_pinned)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                            <i class="bi bi-pin-angle-fill"></i> {{ __('Pinned') }}
                                        </span>
                                    @endif
                                    <span class="break-words">{{ $n->title }}</span>
                                </h3>
                                <span class="shrink-0 text-xs font-medium text-slate-400">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $n->expiry_date ? __('Expires :date', ['date' => $n->expiry_date]) : __('No expiry') }}
                                </span>
                            </div>
                            <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-slate-600">{{ $n->body }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                @if($n->attachment)
                                    <a href="{{ asset('storage/' . $n->attachment) }}" target="_blank"
                                       class="inline-flex items-center gap-1 font-semibold text-indigo-600 hover:text-indigo-700">
                                        <i class="bi bi-paperclip"></i> {{ __('Attachment') }}
                                    </a>
                                @endif
                                <form action="{{ route('notices.destroy', $n) }}" method="POST" class="m-0" onsubmit="confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 font-semibold text-rose-600 hover:text-rose-700">
                                        <i class="bi bi-trash3"></i> {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(method_exists($notices, 'links'))
                    <div class="mt-4">{{ $notices->links() }}</div>
                @endif
            @endif
    {{-- Create Notice Modal --}}
    <div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createNoticeModalLabel">
                        <i class="bi bi-megaphone text-pink-400"></i> {{ __('New Notice') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('notices.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create_notice_title" class="form-label fw-semibold text-slate-700 small">{{ __('Title') }} *</label>
                                <input id="create_notice_title" type="text" name="title" value="{{ old('title') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('Notice headline') }}">
                            </div>
                            <div class="col-12">
                                <label for="create_notice_body" class="form-label fw-semibold text-slate-700 small">{{ __('Body') }} *</label>
                                <textarea id="create_notice_body" name="body" rows="5" required
                                          class="form-control rounded-3" placeholder="{{ __('Write the notice details...') }}">{{ old('body') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="create_notice_expiry" class="form-label fw-semibold text-slate-700 small">{{ __('Expiry Date') }}</label>
                                <input id="create_notice_expiry" type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_notice_attachment" class="form-label fw-semibold text-slate-700 small">{{ __('Attachment') }}</label>
                                <input id="create_notice_attachment" type="file" name="attachment" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label class="d-inline-flex align-items-center gap-2 small text-slate-700">
                                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned')) class="rounded border-slate-300 text-indigo-600">
                                    <i class="bi bi-pin-angle text-warning"></i> {{ __('Pin this notice to the top') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Publish Notice') }}
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
                    var modal = document.getElementById('createNoticeModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
