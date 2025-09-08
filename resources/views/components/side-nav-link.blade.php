<!--Sidebar Highlight onclick -->
@props(['active'])

@php
  $classes = ($active ?? false)
        ? 'bg-blue-600' 
        : 'hover:bg-blue-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->class(['block py-2.5 px-4 rounded'])-> merge(['class' => $classes]) }} >
    {{ $slot }}
</a>