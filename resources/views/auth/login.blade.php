<x-guest-layout>
    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <p>
            <x-text-input id="email"
                type="email"
                name="email"
                :placeholder="__('Email')"
                :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </p>

        <p>
            <x-text-input id="password"
                type="password"
                name="password"
                :placeholder="__('Password')"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </p>

        <label for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            <span>{{ __('Remember me') }}</span>
        </label>

        @if (Route::has('password.request'))
        <p>
            <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        </p>
        @endif

        <footer>
            <x-button-primary>{{ __('Log in') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
