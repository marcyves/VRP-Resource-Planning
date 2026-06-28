<x-guest-layout :title="__('messages.landing_request_access')">
    <x-auth-session-status :status="session('status')" />

    <article class="marketing-auth-card marketing-auth-card--wide">
        <header class="marketing-auth-card__header">
            <h1>{{ __('messages.landing_request_access') }}</h1>
            <p>{{ __('messages.landing_request_lead') }}</p>
        </header>

        <form method="POST" action="{{ route('account-request.store') }}" class="marketing-auth-form nice-form">
            @csrf

            <div class="form-group">
                <x-input-label for="company_name" :value="__('messages.landing_request_company')" />
                <x-text-input id="company_name" name="company_name" type="text" :value="old('company_name')" required />
                <x-input-error :messages="$errors->get('company_name')" />
            </div>

            <div class="form-group">
                <x-input-label for="contact_name" :value="__('messages.landing_request_contact')" />
                <x-text-input id="contact_name" name="contact_name" type="text" :value="old('contact_name')" required />
                <x-input-error :messages="$errors->get('contact_name')" />
            </div>

            <div class="form-group">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" name="email" type="email" :value="old('email')" required autocomplete="email" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="form-group">
                <x-input-label for="phone" :value="__('messages.phone')" />
                <x-text-input id="phone" name="phone" type="tel" :value="old('phone')" autocomplete="tel" />
                <p class="form-hint">{{ __('messages.landing_request_phone_hint') }}</p>
                <x-input-error :messages="$errors->get('phone')" />
            </div>

            <div class="form-group">
                <x-input-label for="terminology_profile" :value="__('messages.landing_request_profile')" />
                <x-terminology-profile-select
                    :selected="old('terminology_profile', \App\Models\Company::PROFILE_EDUCATION)"
                />
                <p class="form-hint">{{ __('messages.landing_request_profile_hint') }}</p>
                <x-input-error :messages="$errors->get('terminology_profile')" />
            </div>

            <div class="form-group">
                <x-input-label for="message" :value="__('messages.landing_request_message')" />
                <textarea id="message" name="message" rows="4" class="form-input">{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" />
            </div>

            <footer class="marketing-auth-form__actions">
                <x-button-primary>{{ __('messages.landing_request_submit') }}</x-button-primary>
            </footer>
        </form>

        <p class="marketing-auth-card__footer">
            {{ __('messages.already_registered') }}
            <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </p>
    </article>
</x-guest-layout>
