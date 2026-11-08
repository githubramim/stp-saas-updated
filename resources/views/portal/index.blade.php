<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal — Results &amp; Attendance</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
<div class="max-w-md mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl shadow-lg mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Student Portal</h1>
        <p class="text-gray-500 mt-1">Check your published results and attendance summary</p>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('portal.show') }}" class="space-y-4">
            @csrf

            <div>
                <label for="reference_no" class="block font-medium text-sm text-gray-700">Registration Number</label>
                <input id="reference_no" name="reference_no" type="text" required autofocus
                       value="{{ old('reference_no') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="e.g. STP-2024-0001" />
                <x-input-error :messages="$errors->get('reference_no')" class="mt-2" />
            </div>

            <div>
                <label for="dob" class="block font-medium text-sm text-gray-700">Date of Birth</label>
                <input id="dob" name="dob" type="date" required
                       value="{{ old('dob') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <x-input-error :messages="$errors->get('dob')" class="mt-2" />
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-md shadow-sm">
                View My Result
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        Results appear here once published by your institute.
    </p>
</div>
</body>
</html>
