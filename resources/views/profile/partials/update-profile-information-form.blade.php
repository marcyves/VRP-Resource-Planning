<section>
    <header>
        <h2 class="card-subtitle">
            {{ __('messages.profile_information') }}
        </h2>

        <p class="form-description">
            {{ __('messages.profile_information_description') }}
        </p>
    </header>

    @if(session()->has('success'))
    <div class="alert alert-success mt-4">
        {{ session()->get('success') }}
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger mt-4">
        {{ session()->get('error')}}
    </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="group-form mt-6">
        @csrf
        @method('patch')

        <div class="form-group">
            <x-input-label for="name" :value="__('messages.name')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="form-group">
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-4">
                <p class="text-sm">
                    {{ __('messages.email_unverified') }}

                    <button form="send-verification" class="nav-link text-sm">
                        {{ __('messages.resend_verification_link') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                <p class="mt-2 status-indicator text-success text-sm">
                    {{ __('messages.verification_link_sent_to_email') }}
                </p>
                @endif
            </div>
            @endif
        </div>

        <div class="form-group">
            <x-input-label for="phone" :value="__('messages.phone')" />
            <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="form-group">
            <x-input-label for="website" :value="__('messages.website')" />
            <x-text-input id="website" name="website" type="text" :value="old('website', $user->website)" autocomplete="url" />
            <x-input-error :messages="$errors->get('website')" />
        </div>

        <div class="form-group">
            <x-input-label for="status_id" :value="__('messages.status')" />
            <select class="form-input" name="status_id" id="status_id">
                @foreach ($statuses as $status)
                <option value="{{$status->id}}" @if ($status->id == old('status_id', $user->status_id)) selected="selected" @endif>
                    {{$status->name}}
                </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status_id')" />
        </div>

        <div class="form-actions mt-6">
            <x-button-primary>{{ __('messages.save') }}</x-button-primary>

            @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="status-indicator text-success text-sm">
                {{ __('messages.saved') }}
            </p>
            @endif
        </div>
    </form>
</section>