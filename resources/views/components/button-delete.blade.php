<button {{ $attributes->merge(['type' => 'submit', 'class' => 'icon icon--delete']) }} aria-label="{{ __('messages.delete') }}">
    <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
</button>