<x-guest-layout>
    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <div class="login-field">
            <x-text-input id="email"
                type="email"
                name="email"
                :placeholder="__('Email')"
                :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="login-field">
            <x-text-input id="password"
                type="password"
                name="password"
                :placeholder="__('Password')"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="login-remember">
            <input id="remember_me" type="checkbox" name="remember">
            <span>{{ __('Remember me') }}</span>
        </label>

        @if (Route::has('password.request'))
        <p class="login-forgot">
            <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        </p>
        @endif

        <footer class="login-actions">
            <x-button-primary>{{ __('Log in') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
