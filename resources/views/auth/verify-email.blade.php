<x-guest-layout>
    <p>{{ __('messages.verify_email_intro') }}</p>

    @if (session('status') == 'verification-link-sent')
    <p role="status">{{ __('messages.verification_link_sent') }}</p>
    @endif

    <footer>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button-primary>{{ __('messages.resend_verification_email') }}</x-button-primary>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button-secondary type="submit">{{ __('messages.logout') }}</x-button-secondary>
        </form>
    </footer>
</x-guest-layout>
