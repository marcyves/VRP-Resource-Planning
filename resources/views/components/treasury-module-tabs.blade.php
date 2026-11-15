@props(['active' => null])

@php
    $schoolId = session('school_id') ?? session('last_school_id');

    $activeTab = $active ?? match (true) {
        request()->routeIs('treasury.invoices.*', 'invoice.create', 'invoice.store', 'invoice.edit', 'invoice.update') => 'invoices',
        default => 'summary',
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('treasury.invoices.index'), 'label' => __('messages.invoices'), 'active' => $activeTab === 'invoices', 'icon' => 'wallet'],
    ['href' => route('treasury.index'), 'label' => __('messages.summary'), 'active' => $activeTab === 'summary', 'icon' => 'chart'],
]" />
