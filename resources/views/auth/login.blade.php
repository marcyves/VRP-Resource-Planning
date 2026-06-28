<x-guest-layout :title="__('messages.login')">
    <x-auth-session-status :status="session('status')" />

    <article class="marketing-auth-card">
        <header class="marketing-auth-card__header">
            <h1>{{ __('messages.login') }}</h1>
            <p>{{ __('messages.landing_login_lead') }}</p>
        </header>

        <form method="POST" action="{{ route('login') }}" class="marketing-auth-form">
            @csrf

            <div class="form-group">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="form-group">
                <x-input-label for="password" :value="__('messages.password')" />
                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <label for="remember_me" class="marketing-auth-form__remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>{{ __('messages.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <p class="marketing-auth-form__meta">
                    <a href="{{ route('password.request') }}">{{ __('messages.forgot_password') }}</a>
                </p>
            @endif

            <footer class="marketing-auth-form__actions">
                <x-button-primary>{{ __('messages.login') }}</x-button-primary>
            </footer>
        </form>

        <p class="marketing-auth-card__footer">
            {{ __('messages.landing_no_account') }}
            <a href="{{ route('account-request.create') }}">{{ __('messages.landing_request_access') }}</a>
        </p>
    </article>
</x-guest-layout>
