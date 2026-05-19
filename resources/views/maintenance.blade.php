<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="maintenance-notice">
        <h2>
            {{ __('messages.maintenance') }}
        </h2>
        <img class="maintenance-notice__logo" src="{{ asset('images/VRP-login.png') }}" alt="VRP" width="200">
        <p>{{ __('messages.maintenance_notice') }}</p>
    </div>
   
</x-guest-layout>
