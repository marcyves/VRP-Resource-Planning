<x-guest-layout :title="__('messages.forgot_password')">
    <article class="marketing-auth-card">
        <header class="marketing-auth-card__header">
            <h1>{{ __('messages.forgot_password') }}</h1>
            <p>{{ __('messages.forgot_password_intro') }}</p>
        </header>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="marketing-auth-form">
            @csrf

            <div class="form-group">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <footer class="marketing-auth-form__actions">
                <x-button-primary>{{ __('messages.email_password_reset_link') }}</x-button-primary>
            </footer>
        </form>

        <p class="marketing-auth-card__footer">
            <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </p>
    </article>
</x-guest-layout>
