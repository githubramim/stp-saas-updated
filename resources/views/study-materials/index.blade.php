<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Digital Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="flex gap-4 items-end flex-wrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                            <select name="course_id" class="rounded-md border-gray-300 text-sm">
                                <option value="">All Courses</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-outline-indigo">Filter</button>
                        </div>
                        <div class="ml-auto">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#createStudyMaterialModal" class="btn btn-solid-indigo">+ Upload Material</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($materials->isEmpty())
                        <p class="text-gray-500 text-center py-8">No study materials uploaded yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($materials as $material)
                                <div class="border rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold truncate">{{ $material->title }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $material->course?->name }}
                                                @if($material->module_name)
                                                    · {{ $material->module_name }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="text-xs bg-gray-100 px-2 py-1 rounded uppercase">{{ $material->file_type }}</span>
                                    </div>
                                    @if($material->description)
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $material->description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-3 text-xs text-gray-500">
                                        <span>{{ $material->uploadedBy?->name }}</span>
                                        <span>{{ $material->created_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <a href="{{ route('study-materials.download', $material) }}" class="btn btn-outline-indigo text-xs flex-1 text-center">Download</a>
                                        <form action="{{ route('study-materials.destroy', $material) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this material?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-red text-xs">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $materials->links() }}</div>
                    @endif
                </div>
            </div>
    {{-- Upload Study Material Modal --}}
    <div class="modal fade" id="createStudyMaterialModal" tabindex="-1" aria-labelledby="createStudyMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createStudyMaterialModalLabel">
                        <i class="bi bi-cloud-arrow-up text-fuchsia-400"></i> {{ __('Upload Study Material') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('study-materials.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create_material_title" class="form-label fw-semibold text-slate-700 small">{{ __('Title') }} *</label>
                                <input id="create_material_title" type="text" name="title" value="{{ old('title') }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_material_course" class="form-label fw-semibold text-slate-700 small">{{ __('Course') }} *</label>
                                <select id="create_material_course" name="course_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Course') }} —</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_material_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch (Optional)') }}</label>
                                <select id="create_material_batch" name="batch_id" class="form-select rounded-3">
                                    <option value="">{{ __('All Batches') }}</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}" @selected(old('batch_id') == $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_material_module" class="form-label fw-semibold text-slate-700 small">{{ __('Module Name (Optional)') }}</label>
                                <input id="create_material_module" type="text" name="module_name" value="{{ old('module_name') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_material_file" class="form-label fw-semibold text-slate-700 small">{{ __('File') }} *</label>
                                <input id="create_material_file" type="file" name="file" required
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                                       class="form-control rounded-3">
                                <p class="mt-1 text-xs text-slate-400">{{ __('PDF, DOC, PPT, XLS, JPG or PNG — max 20 MB.') }}</p>
                            </div>
                            <div class="col-12">
                                <label for="create_material_description" class="form-label fw-semibold text-slate-700 small">{{ __('Description (Optional)') }}</label>
                                <textarea id="create_material_description" name="description" rows="2" class="form-control rounded-3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Upload Material') }}
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
                    var modal = document.getElementById('createStudyMaterialModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
