<x-guest-layout>
    <p>{{ __('messages.confirm_password_intro') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <p>
            <x-input-label for="password" :value="__('messages.password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </p>

        <footer>
            <x-button-primary>{{ __('messages.confirm') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
