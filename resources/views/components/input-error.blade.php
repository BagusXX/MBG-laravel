@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'absolute top-full right-0 mt-1 text-red-500 text-xs']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
