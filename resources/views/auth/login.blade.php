<x-guest-layout>
    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <header class="login-card-header">
            <h2>{{ __('messages.login') }}</h2>
            <img class="login-card-logo" src="{{ asset('images/VRP-login.png') }}" alt="" width="320">
        </header>

        <div class="login-field">
            <x-text-input id="email"
                type="email"
                name="email"
                :placeholder="__('messages.email')"
                :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="login-field">
            <x-text-input id="password"
                type="password"
                name="password"
                :placeholder="__('messages.password')"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="login-remember">
            <input id="remember_me" type="checkbox" name="remember">
            <span>{{ __('messages.remember_me') }}</span>
        </label>

        @if (Route::has('password.request'))
        <p class="login-forgot">
            <a href="{{ route('password.request') }}">{{ __('messages.forgot_password') }}</a>
        </p>
        @endif

        <footer class="login-actions">
            <x-button-primary>{{ __('messages.login') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
