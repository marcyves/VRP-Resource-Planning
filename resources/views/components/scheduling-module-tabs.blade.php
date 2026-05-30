@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('planning.*') => 'planning',
        request()->routeIs('calendar.*') => 'calendar',
        request()->routeIs('billing.byDate') => 'by_date',
        request()->routeIs('billing.*') => 'billing',
        default => null,
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('planning.index'), 'label' => __('messages.planning'), 'active' => $activeTab === 'planning'],
    ['href' => route('calendar.index'), 'label' => __('messages.calendar'), 'active' => $activeTab === 'calendar'],
    ['href' => route('billing.index'), 'label' => __('messages.billing_preparation'), 'active' => $activeTab === 'billing'],
    ['href' => route('billing.byDate'), 'label' => __('messages.by_date'), 'active' => $activeTab === 'by_date'],
]" />
