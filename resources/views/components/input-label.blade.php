@props([
    'text' => null,
    "required" => false,
])

<label
    {{ $attributes->merge(['class' => 'font-normal text-sm text-black']) }}
>
    @if ($text)
        {!! $text !!}@if($required)<span class="text-red-500">*</span>@endif
    @else
        {!! $slot !!}@if($required)<span class="text-red-500">*</span>@endif
    @endif
</label>