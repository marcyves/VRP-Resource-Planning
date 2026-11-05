@props(['controls'])

<button
    type="button"
    class="school-panel__toggle"
    @click="open = !open"
    :aria-expanded="open"
    aria-controls="{{ $controls }}"
    :title="open ? @js(__('actions.hide')) : @js(__('actions.show'))"
    :aria-label="open ? @js(__('actions.hide')) : @js(__('actions.show'))"
>
    <span x-show="open" aria-hidden="true">
        <x-rune-icon name="eye" :size="22" />
    </span>
    <span x-show="!open" aria-hidden="true">
        <x-rune-icon name="eye-off" :size="22" />
    </span>
</button>
