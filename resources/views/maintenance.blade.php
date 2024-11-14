<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="maintenance-notice">
        <h2>
            {{ __('Maintenance') }}
        </h2>
        <p>The site is currently in maintenance, sorry for the inconvenience</p>
    </div>
   
</x-guest-layout>
