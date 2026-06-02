@props(['active' => null])

@php
    $activeTab = $active ?? match (true) {
        request()->routeIs('treasury.reconciliation.*') => 'reconciliation',
        request()->routeIs('treasury.expenses.create', 'treasury.expenses.edit') => 'expense_create',
        request()->routeIs('treasury.reports.*') => 'expense_reports',
        default => 'summary',
    };
@endphp

<x-module-tabs :tabs="[
    ['href' => route('treasury.index') . '#treasury-summary', 'label' => __('messages.summary'), 'active' => $activeTab === 'summary'],
    ['href' => route('treasury.reconciliation.index'), 'label' => __('messages.bank_reconciliation'), 'active' => $activeTab === 'reconciliation'],
    ['href' => route('treasury.index') . '#expense-reports', 'label' => __('messages.expense_reports'), 'active' => $activeTab === 'expense_reports'],
    ['href' => route('treasury.index') . '#standalone-expenses', 'label' => __('messages.standalone_expenses'), 'active' => $activeTab === 'standalone_expenses'],
    ['href' => route('treasury.expenses.create'), 'label' => __('messages.expense_create'), 'active' => $activeTab === 'expense_create'],
]" />
