<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.invoice_list') }} {{ $current_year !== 'all' ? $current_year : '' }}</h2>
    </x-slot>

    <x-invoice-module-tabs active="list" />

    <section class="bills-container">
        <x-table-invoices :invoices="$bills" />
    </section>
</x-app-layout>
