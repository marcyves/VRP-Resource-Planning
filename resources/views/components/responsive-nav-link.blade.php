@props(['active'])

@php
$classes = ($active ?? false)
? 'nav-link-responsive active'
: 'nav-link-responsive';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>