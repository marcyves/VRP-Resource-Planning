<x-modal name="confirm-planning-duplicate" focusable maxWidth="md">
    <div class="profile-modal-form">
        <h2 class="modal-title">{{ __('messages.planning_duplicate_title') }}</h2>
        <p
            class="form-hint"
            x-show="$store.planningDuplicate.label"
            x-text="$store.planningDuplicate.label"
        ></p>
        <form x-bind:action="$store.planningDuplicate.url" method="post" class="planning-duplicate-modal-form">
            @csrf
            <input type="hidden" name="offset" value="custom">
            <div class="form-group">
                <label for="planning-duplicate-date" class="form-label">{{ __('messages.date') }}</label>
                <input
                    type="date"
                    id="planning-duplicate-date"
                    name="date"
                    class="form-input"
                    required
                    x-model="$store.planningDuplicate.date"
                >
            </div>
            <div class="form-actions">
                <x-button-secondary type="button" x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>
                <x-button-primary type="submit">{{ __('messages.duplicate') }}</x-button-primary>
            </div>
        </form>
    </div>
</x-modal>
