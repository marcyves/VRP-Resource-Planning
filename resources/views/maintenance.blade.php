<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="w-full md:w-1/2">
        <h2 class="inline-flex font-semibold text-xl text-gray-800 mr-4">
            {{ __('Maintenance') }}
        </h2>
    </div>
    <p>The site is currently in maintenance, sorry for the inconvenience</p>


    
</x-guest-layout>
