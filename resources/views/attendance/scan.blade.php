<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="font-semibold text-lg text-gray-800">Attendance Check-in</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $session->title }}</p>
        <p class="text-xs text-gray-400">
            {{ $session->session_date->format('d M Y') }}
            @if($session->start_time)
                · {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') }}
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded text-sm">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded text-sm">{{ session('info') }}</div>
    @endif

    <form method="POST" action="{{ route('attendance.scan.store', $session->qr_token) }}">
        @csrf

        <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-md text-sm text-indigo-700">
            Confirm your registration number and date of birth to mark your attendance.
            @if($session->start_time)
                Arriving after
                <strong>{{ \Illuminate\Support\Carbon::parse($session->start_time)->addMinutes($session->late_after_minutes)->format('h:i A') }}</strong>
                will be recorded as late.
            @endif
        </div>

        <div>
            <x-input-label for="reference_no" value="Registration Number *" />
            <x-text-input id="reference_no" class="block mt-1 w-full" type="text" name="reference_no"
                          :value="old('reference_no')" placeholder="e.g. STP-2026-0001" required autofocus />
            <x-input-error :messages="$errors->get('reference_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="dob" value="Date of Birth *" />
            <x-text-input id="dob" class="block mt-1 w-full" type="date" name="dob" :value="old('dob')" required />
            <x-input-error :messages="$errors->get('dob')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>{{ __('Mark My Attendance') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
