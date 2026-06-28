<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.super_admin_company_create') }}</h2>
    </x-slot>

    <section>
        <form action="{{ route('super-admin.companies.store') }}" method="post" class="nice-form super-admin-form">
            @csrf

            <fieldset class="form-section">
                <legend>{{ __('messages.company') }}</legend>

                <div class="form-group">
                    <x-input-label for="company_name">{{ __('messages.super_admin_company_name') }}</x-input-label>
                    <x-text-input id="company_name" name="company_name" type="text" :value="old('company_name')" required />
                    <x-input-error :messages="$errors->get('company_name')" />
                </div>

                <div class="form-group">
                    <x-input-label for="bill_prefix">{{ __('messages.super_admin_bill_prefix') }}</x-input-label>
                    <x-text-input id="bill_prefix" name="bill_prefix" type="text" :value="old('bill_prefix')" maxlength="10" required />
                    <p class="form-hint">{{ __('messages.super_admin_bill_prefix_hint') }}</p>
                    <x-input-error :messages="$errors->get('bill_prefix')" />
                </div>

                <div class="form-group">
                    <x-input-label for="terminology_profile">{{ __('messages.terminology_profile') }}</x-input-label>
                    <x-terminology-profile-select
                        :selected="old('terminology_profile', \App\Models\Company::PROFILE_EDUCATION)"
                        required
                    />
                    <x-input-error :messages="$errors->get('terminology_profile')" />
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>{{ __('messages.super_admin_admin_section') }}</legend>

                <div class="form-group">
                    <x-input-label for="admin_name">{{ __('messages.super_admin_admin_name') }}</x-input-label>
                    <x-text-input id="admin_name" name="admin_name" type="text" :value="old('admin_name')" required />
                    <x-input-error :messages="$errors->get('admin_name')" />
                </div>

                <div class="form-group">
                    <x-input-label for="admin_email">{{ __('messages.super_admin_admin_email') }}</x-input-label>
                    <x-text-input id="admin_email" name="admin_email" type="email" :value="old('admin_email')" required />
                    <x-input-error :messages="$errors->get('admin_email')" />
                </div>

                <div class="form-group">
                    <x-input-label for="admin_password">{{ __('messages.password') }}</x-input-label>
                    <x-text-input id="admin_password" name="admin_password" type="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('admin_password')" />
                </div>

                <div class="form-group">
                    <x-input-label for="admin_password_confirmation">{{ __('messages.confirm_password') }}</x-input-label>
                    <x-text-input id="admin_password_confirmation" name="admin_password_confirmation" type="password" required autocomplete="new-password" />
                </div>
            </fieldset>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.super_admin_company_create') }}</x-button-primary>
                <a href="{{ route('super-admin.companies.index') }}" class="button-secondary">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </section>
</x-app-layout>
