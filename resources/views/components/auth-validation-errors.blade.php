@props(['errors'])

@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200']) }}>
        <div class="font-semibold text-sm text-red-800">
            {{ __('Whoops! Something went wrong.') }}
        </div>

        <ul class="mt-3 space-y-1 list-disc list-inside text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
