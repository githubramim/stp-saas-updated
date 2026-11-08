<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Certificate</h2>
  </x-slot>
  <div class="py-6">
    <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
      <form method="POST" action="{{ route('certificates.store') }}" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        <input name="title" placeholder="Title" class="w-full border p-2 rounded" value="Certificate of Completion" required>
        <input name="issued_date" type="date" class="w-full border p-2 rounded">
        <input name="student_id" placeholder="Student ID (optional)" class="w-full border p-2 rounded">
        <input name="exam_id" placeholder="Exam ID (optional)" class="w-full border p-2 rounded">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
      </form>
    </div>
  </div>
</x-app-layout>