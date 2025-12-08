@props(['disabled' => false])

<input 
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'px-3 py-2 font-normal text-sm text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-400'
    ]) }}
>