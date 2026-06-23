<dialog id="planning-delete-dialog" class="planning-native-dialog">
    <form id="planning-delete-dialog-form" method="post" class="profile-modal-form">
        @csrf
        @method('delete')
        <h2 class="modal-title">{{ __('messages.delete_confirm_title') }}</h2>
        <p class="form-hint" id="planning-delete-dialog-label"></p>
        <p class="form-hint">
            <strong>{{ __('messages.date') }} :</strong>
            <span id="planning-delete-dialog-date"></span>
        </p>
        <p class="form-hint">{{ __('messages.delete_confirm_description_session') }}</p>
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" data-planning-delete-close>
                {{ __('messages.cancel') }}
            </button>
            <button type="submit" class="btn btn-danger">
                {{ __('messages.delete') }}
            </button>
        </div>
    </form>
</dialog>
