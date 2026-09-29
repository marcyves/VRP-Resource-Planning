<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.super_admin_electronic_invoicing_settings') }}</h2>
    </x-slot>

    @if (session('success'))
        <p class="flash flash--success" role="status">{{ session('success') }}</p>
    @endif

    <section class="super-admin-company-show">
        <form
            action="{{ route('super-admin.electronic-invoicing.update') }}"
            method="post"
            class="nice-form super-admin-form"
            autocomplete="off"
        >
            @csrf
            @method('patch')

            <fieldset class="form-section">
                <legend>{{ __('messages.super_admin_webhook_url') }}</legend>
                <p class="form-hint">{{ __('messages.super_admin_webhook_url_hint') }}</p>
                <div class="form-group">
                    <x-input-label for="webhook_url">{{ __('messages.super_admin_webhook_url') }}</x-input-label>
                    <x-text-input
                        id="webhook_url"
                        type="text"
                        :value="$webhookUrl"
                        readonly
                        class="form-input--readonly"
                        onclick="this.select()"
                    />
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>{{ __('messages.super_admin_webhook_secret') }}</legend>
                <p class="form-hint">{{ __('messages.super_admin_webhook_secret_hint') }}</p>

                @if ($secretStoredInUi)
                    <p class="form-hint" role="status">{{ __('messages.super_admin_webhook_secret_stored') }}</p>
                @elseif ($secretPresentInEnv)
                    <p class="form-hint" role="status">{{ __('messages.super_admin_webhook_secret_env_fallback') }}</p>
                @else
                    <p class="form-hint" role="status">{{ __('messages.super_admin_webhook_secret_missing') }}</p>
                @endif

                <div class="form-group">
                    <x-input-label for="superpdp_webhook_secret">{{ __('messages.super_admin_webhook_secret') }}</x-input-label>
                    <x-text-input
                        id="superpdp_webhook_secret"
                        name="superpdp_webhook_secret"
                        type="password"
                        value=""
                        autocomplete="new-password"
                        :placeholder="__('messages.super_admin_webhook_secret_placeholder')"
                    />
                    <x-input-error :messages="$errors->get('superpdp_webhook_secret')" />
                </div>

                @if ($secretStoredInUi)
                    <div class="form-group">
                        <label class="form-hint">
                            <input type="checkbox" name="clear_superpdp_webhook_secret" value="1">
                            {{ __('messages.super_admin_webhook_secret_clear') }}
                        </label>
                    </div>
                @endif
            </fieldset>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.save') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
