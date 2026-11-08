<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notices</h2>
  </x-slot>
  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <a href="{{ route('notices.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">New Notice</a>
      <div class="mt-4 space-y-3">
        @foreach($notices as $n)
        <div class="bg-white p-4 rounded shadow">
          <div class="flex justify-between">
            <h3 class="font-bold">{{ $n->title }} @if($n->is_pinned) 📌 @endif</h3>
            <span class="text-xs text-gray-400">{{ $n->expiry_date }}</span>
          </div>
          <p class="mt-2">{{ $n->body }}</p>
        </div>
        @endforeach
      </div>
      {{ $notices->links() }}
    </div>
  </div>
</x-app-layout>