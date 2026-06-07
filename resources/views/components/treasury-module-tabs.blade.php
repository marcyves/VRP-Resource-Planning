@props(['active' => null])

@php
    $schoolId = session('school_id');
    $createHref = $schoolId
        ? route('invoice.create', ['school_id' => $schoolId])
        : '#';

    $activeTab = $active ?? match (true) {
        request()->routeIs('treasury.invoices.*') => 'invoices',
        request()->routeIs('invoice.create', 'invoice.store') => 'invoice_create',
        request()->routeIs('invoice.edit', 'invoice.update') => 'invoices',
        request()->routeIs('treasury.bank.*', 'treasury.reconciliation.*') => 'bank',
        request()->routeIs('treasury.expenses.create', 'treasury.expenses.edit') => 'expense_create',
        request()->routeIs('treasury.reports.*') => 'expense_reports',
        default => 'summary',
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('treasury.index'), 'label' => __('messages.summary'), 'active' => $activeTab === 'summary', 'icon' => 'chart'],
    ['href' => route('treasury.invoices.index'), 'label' => __('messages.invoices'), 'active' => $activeTab === 'invoices', 'icon' => 'receipt'],
    ['href' => route('invoice.create', $schoolId ? ['school_id' => $schoolId] : []), 'label' => __('messages.invoice_create'), 'active' => $activeTab === 'invoice_create', 'icon' => 'plus', 'disabled' => ! $schoolId],
    ['href' => route('treasury.bank.index'), 'label' => __('messages.bank'), 'active' => $activeTab === 'bank', 'icon' => 'banknote'],
    ['href' => route('treasury.index') . '#expense-reports', 'label' => __('messages.expense_reports'), 'active' => $activeTab === 'expense_reports'],
    ['href' => route('treasury.index') . '#standalone-expenses', 'label' => __('messages.standalone_expenses'), 'active' => $activeTab === 'standalone_expenses'],
    ['href' => route('treasury.expenses.create'), 'label' => __('messages.expense_create'), 'active' => $activeTab === 'expense_create'],
]" />
