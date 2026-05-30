@props(['active', 'icon'])

@php
$classes = ($active ?? false)
    ? 'sidebar-link sidebar-link--active'
    : 'sidebar-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span class="sidebar-link__icon" aria-hidden="true">
        <x-module-tab-icon :name="$icon" />
    </span>
    <span class="sidebar-link__label">{{ $slot }}</span>
</a>
