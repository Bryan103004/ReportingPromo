@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-lg shadow-sm border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition duration-150 px-4 py-3 w-full text-gray-900 placeholder-gray-400']) !!} />
