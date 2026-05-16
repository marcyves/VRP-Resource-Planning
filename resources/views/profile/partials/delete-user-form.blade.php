<section class="section-container">
    <header>
        <h2 class="card-subtitle text-danger">
            {{ __('messages.delete_account') }}
        </h2>

        <p class="form-description mt-2">
            {{ __('messages.delete_account_description') }}
        </p>
    </header>

    <div class="form-actions mt-6">
        <x-button-danger x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('messages.delete_account') }}
        </x-button-danger>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-form p-6">
            @csrf
            @method('delete')

            <h2 class="modal-title">
                {{ __('messages.delete_account_confirm_title') }}
            </h2>

            <p class="form-description mt-4">
                {{ __('messages.delete_account_confirm_description') }}
            </p>

            <div class="form-group mt-6">
                <x-input-label for="password" value="{{ __('messages.password') }}" class="sr-only" />

                <x-text-input id="password" name="password" type="password" placeholder="{{ __('messages.password') }}" />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="modal-actions mt-6">
                <x-button-secondary x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>

                <x-button-danger class="ml-4">
                    {{ __('messages.delete_account') }}
                </x-button-danger>
            </div>
        </form>
    </x-modal>
</section>