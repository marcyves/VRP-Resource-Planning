<dialog id="planning-duplicate-dialog" class="planning-native-dialog">
    <form id="planning-duplicate-dialog-form" method="post" class="profile-modal-form">
        @csrf
        <input type="hidden" name="offset" value="custom">
        <h2 class="modal-title">{{ __('messages.planning_duplicate_title') }}</h2>
        <div class="form-group">
            <label for="planning-duplicate-dialog-date" class="form-label">{{ __('messages.date') }}</label>
            <input
                type="date"
                id="planning-duplicate-dialog-date"
                name="date"
                class="form-input"
                required
            >
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" data-planning-duplicate-close>
                {{ __('messages.cancel') }}
            </button>
            <button type="submit" class="btn btn-primary">
                {{ __('messages.duplicate') }}
            </button>
        </div>
    </form>
</dialog>
