@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('profile.*') => 'profile',
        request()->routeIs('company.*') => 'company',
        default => null,
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('company.show'), 'label' => __('messages.my_company'), 'active' => $activeTab === 'company'],
    ['href' => route('profile.edit'), 'label' => __('messages.profile'), 'active' => $activeTab === 'profile'],
]" />
