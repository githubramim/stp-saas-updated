<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Certificates</h2>
  </x-slot>
  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <a href="{{ route('certificates.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">New Certificate</a>
      <table class="w-full mt-4 border bg-white">
        <thead><tr><th>No</th><th>Title</th><th>Student</th><th>Issued</th><th>Code</th></tr></thead>
        <tbody>
          @foreach($certificates as $c)
          <tr class="border-t">
            <td>{{ $c->certificate_no }}</td>
            <td>{{ $c->title }}</td>
            <td>{{ $c->student?->name_en ?? '-' }}</td>
            <td>{{ $c->issued_date }}</td>
            <td>{{ $c->verification_code }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      {{ $certificates->links() }}
    </div>
  </div>
</x-app-layout>