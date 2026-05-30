@props(['active' => 'list'])

<x-module-tabs :tabs="[
    ['href' => route('invoice.index'), 'label' => __('messages.invoice_list'), 'active' => $active === 'list'],
    ['href' => route('invoice.create'), 'label' => __('messages.invoice_create'), 'active' => $active === 'create'],
]" />
