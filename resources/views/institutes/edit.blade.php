<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Institute Profile') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('institute.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center gap-6 mb-6">
                            <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center overflow-hidden">
                                @if($institute->logo)
                                    <img src="{{ asset('storage/' . $institute->logo) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-2xl font-bold text-indigo-600">{{ substr($institute->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold">{{ $institute->name }}</p>
                                <p class="text-sm text-gray-500">Code: {{ $institute->code }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" value="Institute Name (English) *" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $institute->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="name_bn" value="Institute Name (Bangla)" />
                                <x-text-input id="name_bn" class="block mt-1 w-full" type="text" name="name_bn" :value="old('name_bn', $institute->name_bn)" />
                                <x-input-error :messages="$errors->get('name_bn')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="mobile" value="Mobile *" />
                                <x-text-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile', $institute->mobile)" required />
                                <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="Email *" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $institute->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="address" value="Address" />
                            <textarea id="address" name="address" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $institute->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="logo" value="Institute Logo" />
                            <input type="file" id="logo" name="logo" class="block mt-1 w-full text-sm border border-gray-300 rounded-md p-2" accept="image/*" />
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="btn btn-solid-indigo">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
