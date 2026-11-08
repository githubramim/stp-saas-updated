@php
    $institute = Auth::check() ? Auth::user()->institute : null;
@endphp
@if($institute && $institute->logo)
    <img src="{{ asset('storage/' . $institute->logo) }}" alt="{{ $institute->name }}" {{ $attributes->merge(['class' => 'h-9 w-auto']) }}>
@else
    <div {{ $attributes->merge(['class' => 'h-9 w-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm']) }}>
        {{ $institute ? substr($institute->name, 0, 1) : 'ST' }}
    </div>
@endif
