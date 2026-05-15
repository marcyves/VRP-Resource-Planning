<x-guest-layout>
    <p>{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <p>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </p>

        <footer>
            <x-button-primary>{{ __('Confirm') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
