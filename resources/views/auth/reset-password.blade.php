<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <p>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
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
            <x-button-primary>{{ __('messages.reset_password') }}</x-button-primary>
        </footer>
    </form>
</x-guest-layout>
