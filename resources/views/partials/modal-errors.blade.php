{{-- Shared validation error summary for create modals. --}}
@if($errors->any())
    <div class="mb-3 rounded-3 border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        <strong>{{ __('Please fix the following errors:') }}</strong>
        <ul class="mt-1 list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
