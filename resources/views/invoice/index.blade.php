<x-app-layout>
    <x-slot name="header">
            <h2>
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    <section class="bills">
        <x-table-invoices :invoices=$bills/>
    </section>
</x-app-layout>