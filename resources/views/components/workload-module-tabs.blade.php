@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('school.dashboard') => 'dashboard',
        request()->routeIs('home', 'school.index', 'school.*', 'course.*') => 'schools',
        request()->routeIs('program.*') => 'programs',
        request()->routeIs('group.*') => 'groups',
        default => null,
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('school.dashboard'), 'label' => __('messages.workload_plan'), 'active' => $activeTab === 'dashboard'],
    ['href' => route('home'), 'label' => __('messages.schools'), 'active' => $activeTab === 'schools'],
    ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => $activeTab === 'programs'],
    ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => $activeTab === 'groups'],
]" />
