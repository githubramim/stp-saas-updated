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
                            <a href="{{ route('study-materials.create') }}" class="btn btn-solid-indigo">+ Upload Material</a>
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
        </div>
    </div>
</x-app-layout>
