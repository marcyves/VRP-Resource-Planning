@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('planning.*') => 'planning',
        request()->routeIs('calendar.*') => 'calendar',
        default => null,
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('planning.index'), 'label' => __('messages.planning'), 'active' => $activeTab === 'planning', 'icon' => 'calendar-range'],
    ['href' => route('calendar.index'), 'label' => __('messages.calendar'), 'active' => $activeTab === 'calendar', 'icon' => 'import'],
]" />
