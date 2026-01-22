<section class="section-container">
    <header>
        <h2 class="card-subtitle text-danger">
            {{ __('Delete Account') }}
        </h2>

        <p class="form-description mt-2">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="form-actions mt-6">
        <x-button-danger x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Delete Account') }}
        </x-button-danger>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-form p-6">
            @csrf
            @method('delete')

            <h2 class="modal-title">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="form-description mt-4">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="form-group mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input id="password" name="password" type="password" placeholder="{{ __('Password') }}" />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="modal-actions mt-6">
                <x-button-secondary x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-button-secondary>

                <x-button-danger class="ml-4">
                    {{ __('Delete Account') }}
                </x-button-danger>
            </div>
        </form>
    </x-modal>
</section>