<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Project') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('projects.update', $project) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="name" value="Project Name (English) *" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $project->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="name_bn" value="Project Name (Bangla)" />
                            <x-text-input id="name_bn" class="block mt-1 w-full" type="text" name="name_bn" :value="old('name_bn', $project->name_bn)" />
                            <x-input-error :messages="$errors->get('name_bn')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="code" value="Project Code" />
                            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $project->code)" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $project->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" name="description" rows="3">{{ old('description', $project->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mt-6 rounded-xl bg-indigo-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-indigo-700">
                            <i class="bi bi-bank me-1"></i> Donor, Budget &amp; MoU (Project-Based Training)
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="donor_name" value="Donor Name" />
                                <x-text-input id="donor_name" class="block mt-1 w-full" type="text" name="donor_name" :value="old('donor_name', $project->donor_name)" />
                                <x-input-error :messages="$errors->get('donor_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="donor_type" value="Donor Type" />
                                <select id="donor_type" name="donor_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select type</option>
                                    @foreach(['government' => 'Government', 'ngo' => 'NGO / Development Partner', 'corporate' => 'Corporate', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('donor_type', $project->donor_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('donor_type')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="budget_amount" value="Budget (৳)" />
                                <x-text-input id="budget_amount" class="block mt-1 w-full" type="number" step="0.01" min="0" name="budget_amount" :value="old('budget_amount', $project->budget_amount)" />
                                <x-input-error :messages="$errors->get('budget_amount')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="target_trainees" value="Target Trainees" />
                                <x-text-input id="target_trainees" class="block mt-1 w-full" type="number" min="0" name="target_trainees" :value="old('target_trainees', $project->target_trainees)" />
                                <x-input-error :messages="$errors->get('target_trainees')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="mou_number" value="MoU Number" />
                                <x-text-input id="mou_number" class="block mt-1 w-full" type="text" name="mou_number" :value="old('mou_number', $project->mou_number)" />
                                <x-input-error :messages="$errors->get('mou_number')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="mou_file" value="MoU Document" />
                                <input id="mou_file" type="file" name="mou_file" accept=".pdf,.jpg,.jpeg,.png" class="block mt-1 w-full rounded-md border border-gray-300 p-2 text-sm">
                                @if($project->mou_file_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($project->mou_file_path) }}" target="_blank" class="mt-1 inline-block text-xs font-semibold text-indigo-600 hover:underline">
                                        <i class="bi bi-paperclip"></i> Current MoU document
                                    </a>
                                @endif
                                <x-input-error :messages="$errors->get('mou_file')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="start_date" value="Start Date" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $project->start_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="End Date" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $project->end_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="coordinator_name" value="Coordinator Name" />
                                <x-text-input id="coordinator_name" class="block mt-1 w-full" type="text" name="coordinator_name" :value="old('coordinator_name', $project->coordinator_name)" />
                                <x-input-error :messages="$errors->get('coordinator_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="coordinator_contact" value="Coordinator Contact" />
                                <x-text-input id="coordinator_contact" class="block mt-1 w-full" type="text" name="coordinator_contact" :value="old('coordinator_contact', $project->coordinator_contact)" />
                                <x-input-error :messages="$errors->get('coordinator_contact')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-amber">Update Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
