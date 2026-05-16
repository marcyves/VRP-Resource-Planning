<x-guest-layout>
    <p>{{ __('messages.forgot_password_intro') }}</p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <p>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </p>

        <footer>
            <x-button-primary>{{ __('messages.email_password_reset_link') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
