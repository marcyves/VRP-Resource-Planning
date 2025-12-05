@props(['active'])

@php
$classes = ($active ?? true)
            ? 'link-item link-active'
            : 'link-item';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
