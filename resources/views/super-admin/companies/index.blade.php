<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.super_admin_companies_list') }}</h2>
    </x-slot>

    <section class="page-toolbar">
        <a href="{{ route('super-admin.companies.create') }}" class="button-primary">
            {{ __('messages.super_admin_company_create') }}
        </a>
    </section>

    @if (session('success'))
        <p class="flash flash--success" role="status">{{ session('success') }}</p>
    @endif

    <section>
        @if ($companies->isEmpty())
            <p role="status">{{ __('messages.super_admin_no_companies') }}</p>
        @else
            <ul class="resource-grid">
                @foreach ($companies as $company)
                    @php
                        $admin = $company->users->first();
                    @endphp
                    <li class="resource-card">
                        <header class="resource-card__header">
                            <h3 class="resource-card__title">
                                <a href="{{ route('super-admin.companies.show', $company) }}">{{ $company->name }}</a>
                            </h3>
                            <p class="resource-card__meta">{{ $company->bill_prefix }}</p>
                        </header>
                        <dl class="resource-card__stats">
                            <div>
                                <dt>{{ __('messages.super_admin_admin_email') }}</dt>
                                <dd>{{ $admin?->email ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('messages.super_admin_users_count') }}</dt>
                                <dd>{{ $company->users_count }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('messages.terminology_profile') }}</dt>
                                <dd>{{ $company->terminologyProfileLabel() }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-app-layout>
