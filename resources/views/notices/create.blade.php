<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-megaphone text-pink-500"></i>
                <span class="truncate">{{ __('New Notice') }}</span>
            </h2>
            <a href="{{ route('notices.index') }}" class="btn-outline-gray shrink-0">
                <i class="bi bi-arrow-left"></i>
                {{ __('Back to Notices') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('notices.store') }}" enctype="multipart/form-data"
                  class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                @csrf

                <div>
                    <label for="title" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Title') }} <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="{{ __('Notice headline') }}">
                    @error('title') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Body') }} <span class="text-rose-500">*</span></label>
                    <textarea id="body" name="body" rows="6" required
                              class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="{{ __('Write the notice details...') }}">{{ old('body') }}</textarea>
                    @error('body') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="expiry_date" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Expiry Date') }}</label>
                        <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date') }}"
                               class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('expiry_date') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="attachment" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Attachment') }}</label>
                        <input id="attachment" name="attachment" type="file"
                               class="w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-600 shadow-sm file:me-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('attachment') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))
                           class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <i class="bi bi-pin-angle text-amber-500"></i>
                    {{ __('Pin this notice to the top') }}
                </label>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('notices.index') }}" class="btn-outline-gray">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn-solid-indigo">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ __('Publish Notice') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
