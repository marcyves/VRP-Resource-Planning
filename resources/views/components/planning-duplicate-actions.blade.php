@props([
    'planningId',
    'eventLabel' => '',
    'defaultDate' => '',
    'variant' => 'compact',
])

@php
    $duplicateUrl = route('planning.duplicate', $planningId);
@endphp

@if ($variant === 'inline')
    <div {{ $attributes->merge(['class' => 'planning-duplicate-actions planning-duplicate-actions--inline']) }}>
        <span class="form-label">{{ __('messages.duplicate') }}</span>
        <div class="planning-duplicate-actions__buttons">
            <form action="{{ $duplicateUrl }}" method="post">
                @csrf
                <input type="hidden" name="offset" value="tomorrow">
                <x-button-secondary type="submit">{{ __('messages.planning_duplicate_tomorrow') }}</x-button-secondary>
            </form>
            <form action="{{ $duplicateUrl }}" method="post">
                @csrf
                <input type="hidden" name="offset" value="next_week">
                <x-button-secondary type="submit">{{ __('messages.planning_duplicate_next_week') }}</x-button-secondary>
            </form>
            <x-button-secondary
                type="button"
                x-data=""
                x-on:click.prevent="$store.planningDuplicate.request(@js($duplicateUrl), @js($eventLabel), @js($defaultDate))"
            >
                {{ __('messages.planning_duplicate_custom_date') }}
            </x-button-secondary>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'planning-quick-actions']) }} role="group" aria-label="{{ __('messages.duplicate') }}">
        <form action="{{ $duplicateUrl }}" method="post">
            @csrf
            <input type="hidden" name="offset" value="tomorrow">
            <button type="submit" class="planning-quick-action" title="{{ __('messages.planning_duplicate_tomorrow') }}">
                +1j
            </button>
        </form>
        <form action="{{ $duplicateUrl }}" method="post">
            @csrf
            <input type="hidden" name="offset" value="next_week">
            <button type="submit" class="planning-quick-action" title="{{ __('messages.planning_duplicate_next_week') }}">
                +1s
            </button>
        </form>
        <button
            type="button"
            class="planning-quick-action"
            title="{{ __('messages.planning_duplicate_custom_date') }}"
            x-data=""
            x-on:click.prevent="$store.planningDuplicate.request(@js($duplicateUrl), @js($eventLabel), @js($defaultDate))"
        >
            …
        </button>
    </div>
@endif
