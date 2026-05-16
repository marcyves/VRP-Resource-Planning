<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">
            {{ __('messages.profile') }}
        </h2>
    </x-slot>

    <section>
        <div class="profile-form-container">
            @include('profile.partials.update-profile-information-form')
        </div>
    </section>

    <section>
        <div class="profile-form-container">
            @include('profile.partials.update-password-form')
        </div>
    </section>

    <section>
        <div class="profile-form-container">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</x-app-layout>