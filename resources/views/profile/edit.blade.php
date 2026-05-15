<x-app-layout>
<x-slot name="header">
        <h2 class="header-title">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="profile-container">
        <div class="profile-section-list">
            <div class="profile-section-card">
                <div class="profile-form-container">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="profile-section-card">
                <div class="profile-form-container">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="profile-section-card">
                <div class="profile-form-container">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>