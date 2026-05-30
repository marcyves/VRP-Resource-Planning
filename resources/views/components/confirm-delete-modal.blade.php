@props([
    'name',
    'store',
    'title',
    'description',
    'hints' => [],
])

<x-modal :name="$name" focusable maxWidth="md">
    <div class="profile-modal-form">
        <h2 class="modal-title">{{ $title }}</h2>
        @foreach ($hints as $hint)
            @if (! empty($hint['plain']))
                <p
                    class="form-hint"
                    x-show="$store.{{ $store }}.{{ $hint['field'] }}"
                    x-text="$store.{{ $store }}.{{ $hint['field'] }}"
                ></p>
            @else
                <p class="form-hint" x-show="$store.{{ $store }}.{{ $hint['field'] }}">
                    <strong>{{ $hint['label'] }} :</strong>
                    <span x-text="$store.{{ $store }}.{{ $hint['field'] }}"></span>
                </p>
            @endif
        @endforeach
        <p class="form-hint">{{ $description }}</p>
        <div class="form-actions">
            <x-button-secondary type="button" x-on:click="$dispatch('close')">
                {{ __('messages.cancel') }}
            </x-button-secondary>
            <form x-bind:action="$store.{{ $store }}.url" method="post">
                @csrf
                @method('delete')
                <x-button-danger type="submit">{{ __('messages.delete') }}</x-button-danger>
            </form>
        </div>
    </div>
</x-modal>
