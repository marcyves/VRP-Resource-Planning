<x-guest-layout>
    <p>{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>

    @if (session('status') == 'verification-link-sent')
    <p role="status">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</p>
    @endif

    <footer>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button-primary>{{ __('Resend Verification Email') }}</x-button-primary>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button-secondary type="submit">{{ __('Log Out') }}</x-button-secondary>
        </form>
    </footer>
</x-guest-layout>
