@extends('layouts.apply')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-full mb-6">
        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Application Submitted!</h2>
    <p class="text-gray-500 mb-6">Your application has been received successfully.</p>

    <div class="inline-block bg-indigo-50 rounded-xl px-8 py-4 mb-6">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reference No.</p>
        <p class="text-2xl font-bold text-indigo-600 tracking-widest">{{ $referenceNo }}</p>
    </div>

    <p class="text-sm text-gray-500 mb-6">Please save this reference number for future correspondence.</p>

    <a href="{{ route('apply') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-xl transition duration-150 shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Submit Another Application
    </a>
</div>

@endsection
