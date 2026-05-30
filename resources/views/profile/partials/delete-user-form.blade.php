<section>
    <header>
        <h2 class="profile-form__title profile-form__title--danger">
            {{ __('messages.delete_account') }}
        </h2>
        <p class="form-hint">
            {{ __('messages.delete_account_description') }}
        </p>
    </header>

    <div class="form-actions profile-form__danger-actions">
        <x-button-danger x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('messages.delete_account') }}
        </x-button-danger>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="profile-modal-form">
            @csrf
            @method('delete')

            <h2 class="modal-title">
                {{ __('messages.delete_account_confirm_title') }}
            </h2>

            <p class="form-hint">
                {{ __('messages.delete_account_confirm_description') }}
            </p>

            <div class="form-group">
                <x-input-label for="delete_password" value="{{ __('messages.password') }}" class="sr-only" />
                <x-text-input id="delete_password" name="password" type="password" placeholder="{{ __('messages.password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="form-actions">
                <x-button-secondary x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>
                <x-button-danger type="submit">
                    {{ __('messages.delete_account') }}
                </x-button-danger>
            </div>
        </form>
    </x-modal>
</section>
