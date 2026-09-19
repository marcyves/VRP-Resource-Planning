@props([
    'school',
    'panel' => 'courses',
])

@php
    $base = route('school.show', $school);
    $tabs = [
        [
            'href' => $base.'?panel=courses',
            'label' => \App\Support\SchoolContext::msg($school, 'course_list'),
            'active' => $panel === 'courses',
            'icon' => 'layers',
        ],
        [
            'href' => $base.'?panel=groups',
            'label' => \App\Support\SchoolContext::msg($school, 'groups'),
            'active' => $panel === 'groups',
            'icon' => 'users',
        ],
        [
            'href' => $base.'?panel=details',
            'label' => __('messages.details'),
            'active' => $panel === 'details',
            'icon' => 'building',
        ],
        [
            'href' => $base.'?panel=documents',
            'label' => __('messages.documents'),
            'active' => $panel === 'documents',
            'icon' => 'folder',
        ],
    ];
@endphp

<x-module-tabs :tabs="$tabs" />
