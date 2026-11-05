@props(['label' => null])
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'icon icon--delete']) }} aria-label="{{ $label ?? __('messages.delete') }}">
    <x-rune-icon name="trash" :size="18" />
</button>
