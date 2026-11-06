@props(['school_id'])

<form action="{{ route('invoice.create') }}" method="get" class="manual-invoice-form">
    <input type="hidden" name="school_id" value="{{ $school_id }}">
    <button type="submit" class="icon icon--invoice-manual" aria-label="{{ __('messages.invoice_create') }}">
        <x-rune-icon name="invoice-manual" :size="20" />
    </button>
</form>
