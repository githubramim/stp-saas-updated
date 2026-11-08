<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Class Schedule') }}
            @if($project)
                <span class="text-sm font-normal text-gray-400 ml-2">— {{ $project->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('schedules.store') }}">
                        @csrf

                        @include('schedules._form', ['schedule' => null])

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('schedules.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-emerald">Save Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>