@props(['icon' => 'inbox'])

<span {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span class="empty-state__icon" aria-hidden="true">
        <x-rune-icon :name="$icon" />
    </span>
    <span class="empty-state__text">{!! $slot !!}</span>
</span>
