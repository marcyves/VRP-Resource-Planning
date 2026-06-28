<x-app-layout>
    <x-slot name="header">
        <h2>{{ $company->name }}</h2>
    </x-slot>

    <section class="page-toolbar">
        <a href="{{ route('super-admin.companies.index') }}" class="button-secondary">
            {{ __('messages.super_admin_back_to_companies') }}
        </a>
    </section>

    @if (session('success'))
        <p class="flash flash--success" role="status">{{ session('success') }}</p>
    @endif

    <section class="super-admin-company-show">
        <dl class="resource-card__stats">
            <div>
                <dt>{{ __('messages.super_admin_bill_prefix') }}</dt>
                <dd>{{ $company->bill_prefix }}</dd>
            </div>
            <div>
                <dt>{{ __('messages.super_admin_users_count') }}</dt>
                <dd>{{ $company->users->count() }}</dd>
            </div>
        </dl>

        <form
            action="{{ route('super-admin.companies.update', $company) }}"
            method="post"
            class="nice-form super-admin-form"
        >
            @csrf
            @method('patch')

            <fieldset class="form-section">
                <legend>{{ __('messages.terminology_profile') }}</legend>

                <div class="form-group">
                    <x-input-label for="terminology_profile">{{ __('messages.terminology_profile') }}</x-input-label>
                    <x-terminology-profile-select
                        :selected="old('terminology_profile', $company->terminology_profile)"
                        required
                    />
                    <x-input-error :messages="$errors->get('terminology_profile')" />
                </div>
            </fieldset>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.save') }}</x-button-primary>
            </div>
        </form>

        <section class="form-section">
            <h3>{{ __('messages.super_admin_company_users') }}</h3>

            @if ($company->users->isEmpty())
                <p role="status">{{ __('messages.super_admin_no_users') }}</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.email') }}</th>
                            <th scope="col">{{ __('messages.super_admin_user_role') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->getStatusName() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="form-section">
            <h3>{{ __('messages.super_admin_add_user') }}</h3>

            <form
                action="{{ route('super-admin.companies.users.store', $company) }}"
                method="post"
                class="nice-form super-admin-form"
            >
                @csrf

                <div class="form-group">
                    <x-input-label for="user_name">{{ __('messages.name') }}</x-input-label>
                    <x-text-input id="user_name" name="name" type="text" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="form-group">
                    <x-input-label for="user_email">{{ __('messages.email') }}</x-input-label>
                    <x-text-input id="user_email" name="email" type="email" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="form-group">
                    <x-input-label for="user_status_id">{{ __('messages.super_admin_user_role') }}</x-input-label>
                    <select id="user_status_id" name="status_id" class="form-input" required>
                        @foreach ($roleOptions as $statusId => $label)
                            <option value="{{ $statusId }}" @selected((int) old('status_id', \App\Models\Status::READER) === $statusId)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status_id')" />
                </div>

                <div class="form-group">
                    <x-input-label for="user_password">{{ __('messages.password') }}</x-input-label>
                    <x-text-input id="user_password" name="password" type="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="form-group">
                    <x-input-label for="user_password_confirmation">{{ __('messages.confirm_password') }}</x-input-label>
                    <x-text-input id="user_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                </div>

                <div class="form-actions">
                    <x-button-primary>{{ __('messages.super_admin_add_user') }}</x-button-primary>
                </div>
            </form>
        </section>

        <section class="form-section form-section--danger">
            <h3>{{ __('messages.super_admin_danger_zone') }}</h3>
            <p class="form-hint">{{ __('messages.super_admin_company_delete_hint') }}</p>
            <form
                action="{{ route('super-admin.companies.destroy', $company) }}"
                method="post"
                onsubmit="return confirm(@json(__('messages.super_admin_company_delete_confirm', ['name' => $company->name])));"
            >
                @csrf
                @method('delete')
                <x-button-danger type="submit">{{ __('messages.super_admin_company_delete') }}</x-button-danger>
            </form>
        </section>
    </section>
</x-app-layout>
