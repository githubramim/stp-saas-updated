<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Notice</h2>
  </x-slot>
  <div class="py-6">
    <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
      <form method="POST" action="{{ route('notices.store') }}" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        <input name="title" placeholder="Title" class="w-full border p-2 rounded" required>
        <textarea name="body" placeholder="Body" class="w-full border p-2 rounded" rows="5" required></textarea>
        <input name="expiry_date" type="date" class="w-full border p-2 rounded">
        <label class="flex items-center gap-2"><input type="checkbox" name="is_pinned" value="1"> Pin to top</label>
        <input type="file" name="attachment" class="w-full border p-2 rounded">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
      </form>
    </div>
  </div>
</x-app-layout>