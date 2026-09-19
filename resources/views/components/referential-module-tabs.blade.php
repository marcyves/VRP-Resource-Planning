@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('program.*') => 'programs',
        request()->routeIs('group.*') => 'groups',
        default => null,
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => $activeTab === 'programs', 'icon' => 'layers'],
    ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => $activeTab === 'groups', 'icon' => 'users'],
]" />
