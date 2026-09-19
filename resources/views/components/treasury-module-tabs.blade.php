@props(['active' => null])

@php
    $schoolId = session('school_id') ?? session('last_school_id');

    $activeTab = $active ?? match (true) {
        request()->routeIs('treasury.invoices.*') => 'invoices',
        request()->routeIs('invoice.create', 'invoice.store', 'invoice.edit', 'invoice.update') => 'invoices',
        request()->routeIs('treasury.bank.*', 'treasury.reconciliation.*') => 'bank',
        request()->routeIs('treasury.expenses.*', 'treasury.reports.*') => 'expenses',
        default => 'summary',
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('treasury.index'), 'label' => __('messages.summary'), 'active' => $activeTab === 'summary', 'icon' => 'chart'],
    ['href' => route('treasury.invoices.index'), 'label' => __('messages.invoices'), 'active' => $activeTab === 'invoices', 'icon' => 'receipt'],
    ['href' => route('treasury.bank.index'), 'label' => __('messages.bank'), 'active' => $activeTab === 'bank', 'icon' => 'banknote'],
    ['href' => route('treasury.index').'#expense-reports', 'label' => __('messages.expenses'), 'active' => $activeTab === 'expenses', 'icon' => 'coins'],
]" />
