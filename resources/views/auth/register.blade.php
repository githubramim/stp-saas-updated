<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4 text-center">
            <h4 class="font-semibold text-gray-800">STP Registration</h4>
            <p class="text-sm text-gray-500">Register your institute for admission management</p>
        </div>

        <div class="border-b border-gray-200 pb-2 mb-4">
            <p class="text-sm font-medium text-gray-600">Institute Information</p>
        </div>

        <div>
            <x-input-label for="institute_name" :value="__('Institute Name *')" />
            <x-text-input id="institute_name" class="block mt-1 w-full" type="text" name="institute_name" :value="old('institute_name')" required autofocus />
            <x-input-error :messages="$errors->get('institute_name')" class="mt-2" />
        </div>

        <div class="mt-3">
            <x-input-label for="institute_code" :value="__('Institute Code *')" />
            <x-text-input id="institute_code" class="block mt-1 w-full" type="text" name="institute_code" :value="old('institute_code')" required placeholder="e.g. STP001" />
            <x-input-error :messages="$errors->get('institute_code')" class="mt-2" />
        </div>

        <div class="mt-3">
            <x-input-label for="mobile" :value="__('Mobile Number *')" />
            <x-text-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile')" required placeholder="01XXXXXXXXX" />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <div class="border-b border-gray-200 pb-2 mt-4 mb-4">
            <p class="text-sm font-medium text-gray-600">Admin Account</p>
        </div>

        <div>
            <x-input-label for="admin_name" :value="__('Admin Name *')" />
            <x-text-input id="admin_name" class="block mt-1 w-full" type="text" name="admin_name" :value="old('admin_name')" required />
            <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
        </div>

        <div class="mt-3">
            <x-input-label for="email" :value="__('Email *')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-3">
            <x-input-label for="password" :value="__('Password *')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-3">
            <x-input-label for="password_confirmation" :value="__('Confirm Password *')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>