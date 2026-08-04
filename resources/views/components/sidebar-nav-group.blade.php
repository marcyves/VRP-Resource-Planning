@props([
    'active' => false,
])

@php
    $open = $active || request()->routeIs('program.*', 'group.*');
@endphp

<div
    class="sidebar-nav-group {{ $open ? 'sidebar-nav-group--open' : '' }}"
    data-sidebar-group
>
    <button
        type="button"
        class="sidebar-link sidebar-link--button sidebar-nav-group__toggle {{ $open ? 'sidebar-link--active' : '' }}"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        data-sidebar-group-toggle
        title="{{ __('messages.nav_referential') }}"
        aria-label="{{ __('messages.nav_referential') }}"
    >
        <span class="sidebar-link__icon" aria-hidden="true">
            <x-module-tab-icon name="folder" />
        </span>
        <span class="sidebar-link__label">{{ __('messages.nav_referential') }}</span>
    </button>

    <div class="sidebar-nav-group__items" @if (! $open) hidden @endif data-sidebar-group-panel>
        <x-sidebar-nav-link
            icon="layers"
            :href="route('program.index')"
            :active="request()->routeIs('program.*')"
            class="sidebar-link--nested"
        >
            {{ __('messages.programs') }}
        </x-sidebar-nav-link>
        <x-sidebar-nav-link
            icon="users"
            :href="route('group.index')"
            :active="request()->routeIs('group.*')"
            class="sidebar-link--nested"
        >
            {{ __('messages.groups') }}
        </x-sidebar-nav-link>
    </div>
</div>
