@props(['paid' => false])

<button class="icon {{ $paid ? 'icon--payment-restore' : 'icon--payed' }}" type="submit" aria-label="{{ $paid ? __('messages.cancel_payment') : __('messages.mark_as_paid') }}">
    @if($paid)
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M6.5 8.5h8.25a4.75 4.75 0 1 1 0 9.5H9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M9.25 5.75 6.5 8.5l2.75 2.75" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 12.25h3.25" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
    </svg>
    @else
    <img src="{{ asset('icons/payed.svg') }}" alt="" width="20" height="20" decoding="async">
    @endif
</button>
