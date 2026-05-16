<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <p>
            <x-input-label for="name" :value="__('messages.name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </p>

        <p>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </p>

        <p>
            <x-input-label for="password" :value="__('messages.password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </p>

        <p>
            <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </p>

        <footer>
            <a href="{{ route('login') }}">{{ __('messages.already_registered') }}</a>
            <x-button-primary>{{ __('messages.register') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
