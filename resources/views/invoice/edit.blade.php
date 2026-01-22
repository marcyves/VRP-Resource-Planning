<x-app-layout>
    @push('styles')
    @vite(['resources/css/bills.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.bills') }}</h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section class="glass-background">
        <form action="{{route('invoice.update', $invoice)}}" method="post" class="group-form glass-background-solid">
            @csrf
            @method('patch')

            <div class="form-group">
                <span class="form-label">{{ __('messages.invoice_id') }}: {{ $invoice->id }}</span>
                <input type="hidden" name="id" id="id" value="{{ $invoice->id }}">
            </div>

            <div class="form-group">
                <x-input-label for="description">{{ __('messages.description') }}</x-input-label>
                <x-text-input type="text" name="description" id="description" value="{{ $invoice->description }}" />
            </div>

            <div class="form-group">
                <x-input-label for="amount">{{ __('messages.gain') }}</x-input-label>
                <x-text-input type="text" name="amount" id="amount" value="{{ $invoice->amount }}" />
            </div>

            <div class="form-group">
                <x-input-label for="created_at">{{ __('messages.created_at') }}</x-input-label>
                <x-text-input type="datetime-local" name="created_at" id="created_at" value="{{ $invoice->created_at }}" />
            </div>

            <div class="form-group">
                <x-input-label for="paid_at">{{ __('messages.paid_at') }}</x-input-label>
                <x-text-input type="datetime-local" name="paid_at" id="paid_at" value="{{ $invoice->paid_at }}" />
            </div>

            <x-button-primary>{{ __('messages.update') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>