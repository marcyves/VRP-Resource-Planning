<x-app-layout>
    <x-slot name="header">
        <h2 class="header-title">{{ __('messages.profile') }}</h2>
    </x-slot>

    <x-settings-module-tabs active="profile" />

    <section class="profile-form-container nice-form">
        @include('profile.partials.update-profile-information-form')
    </section>

    <section class="profile-form-container nice-form">
        @include('profile.partials.update-password-form')
    </section>

    <section class="profile-form-container nice-form">
        @include('profile.partials.delete-user-form')
    </section>
</x-app-layout>
