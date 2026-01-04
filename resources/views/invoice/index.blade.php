<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.invoice_list') }}
        </h2>
    </x-slot>

    <section class="bills glass-background">
        <x-table-invoices :invoices=$bills />
    </section>
</x-app-layout>