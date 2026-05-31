@props(['active' => 'list'])

@php
    $schoolId = session('school_id');
    $createHref = $schoolId
        ? route('invoice.create', ['school_id' => $schoolId])
        : '#';
@endphp

<x-module-tabs :tabs="[
    ['href' => route('invoice.index'), 'label' => __('messages.invoice_list'), 'active' => $active === 'list', 'icon' => 'receipt'],
    ['href' => $createHref, 'label' => __('messages.invoice_create'), 'active' => $active === 'create', 'icon' => 'plus', 'disabled' => ! $schoolId],
]" />
