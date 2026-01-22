<x-app-layout>
    @push('styles')
    @vite(['resources/css/bills.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.invoice_list') }}</h2>
    </x-slot>

    <section class="bills-container glass-background">
        <x-table-invoices :invoices="$bills" />
    </section>
</x-app-layout>