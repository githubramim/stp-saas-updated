<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 gap-sm-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-laptop text-rose-600"></i>
                <span class="truncate">{{ __('Lab & Resources') }}</span>
            </h2>
            <button type="button" class="btn btn-danger font-bold text-white shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createResourceModal">
                <i class="bi bi-plus-circle-fill me-1"></i> + Add Resource
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark mb-0">Lab Inventory Equipment List</h4>
                    </div>

                    @if($resources->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No lab resources added yet. Click <strong>+ Add Resource</strong> to track new equipment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 640px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Item Name</th>
                                        <th>Lab / Location</th>
                                        <th>Condition</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resources as $resource)
                                        <tr>
                                            <td class="font-monospace text-primary fw-bold">{{ $resource->resource_code }}</td>
                                            <td class="fw-bold text-dark">{{ $resource->resource_name }}</td>
                                            <td class="text-muted">{{ $resource->location_lab }}</td>
                                            <td>
                                                <span class="badge 
                                                    {{ $resource->condition === 'good' ? 'bg-success' : '' }}
                                                    {{ $resource->condition === 'damaged' ? 'bg-danger' : '' }}
                                                    {{ $resource->condition === 'under_maintenance' ? 'bg-warning text-dark' : '' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $resource->condition)) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('resources.edit', $resource) }}" class="btn btn-sm btn-outline-warning py-0 px-2">Edit</a>
                                                    <form action="{{ route('resources.destroy', $resource) }}" method="POST" class="d-inline" onsubmit="confirmDelete(event)">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
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
    </div>

    {{-- Create Resource Modal --}}
    <div class="modal fade" id="createResourceModal" tabindex="-1" aria-labelledby="createResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white flex items-center gap-2" id="createResourceModalLabel">
                        <i class="bi bi-plus-circle text-danger"></i> Add New Lab Equipment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('resources.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            {{-- Resource Code --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Equipment Code *</label>
                                <input type="text" name="resource_code" class="form-control rounded-3 font-monospace" required placeholder="e.g. PC-LAB1-05">
                            </div>

                            {{-- Resource Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Equipment Name *</label>
                                <input type="text" name="resource_name" class="form-control rounded-3" required placeholder="e.g. Core i7 Workstation PC">
                            </div>

                            {{-- Location Lab --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Lab / Room Location *</label>
                                <input type="text" name="location_lab" class="form-control rounded-3" required placeholder="e.g. Lab-1 (Software)">
                            </div>

                            {{-- Condition --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Working Condition *</label>
                                <select name="condition" class="form-select rounded-3" required>
                                    <option value="good" selected>Good Condition</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="under_maintenance">Under Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger text-white rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Save Equipment
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
                    var resourceModal = document.getElementById('createResourceModal');
                    if (resourceModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(resourceModal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
