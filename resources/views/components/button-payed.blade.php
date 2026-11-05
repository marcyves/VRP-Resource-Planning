@props(['paid' => false])

<button class="icon {{ $paid ? 'icon--payment-restore' : 'icon--payed' }}" type="submit" aria-label="{{ $paid ? __('messages.cancel_payment') : __('messages.mark_as_paid') }}">
    <x-rune-icon :name="$paid ? 'payment-restore' : 'payed'" :size="20" />
</button>
