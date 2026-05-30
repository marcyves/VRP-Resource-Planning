<x-app-layout>
    <x-slot name="header">
        <div class="settings-page-header">
            <h2 class="header-title">{{ __('messages.my_company') }}</h2>
            @if (Auth::user()->isAdmin() || Auth::user()->isEditor())
                <div class="settings-page-header__actions">
                    <form action="{{ route('company.edit') }}" method="get">
                        <x-button-edit />
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <x-settings-module-tabs active="company" />

    <section class="company-show-grid">
        <article class="company-show-card">
            <h3>{{ __('messages.my_company') }}</h3>
            <p class="company-show-card__lead">{{ $company->name }}</p>
            <p class="form-hint">
                {{ __('messages.terminology_profile') }}:
                {{ ($company->terminology_profile ?? 'education') === 'consulting'
                    ? __('messages.terminology_profile_consulting')
                    : __('messages.terminology_profile_education') }}
            </p>
            <ul>
                <li>{{ $company->address }}</li>
                <li>{{ $company->zip }} {{ $company->city }}</li>
                <li>{{ $company->country }}</li>
            </ul>
        </article>

        <article class="company-show-card">
            <h3>{{ __('messages.contact') }}</h3>
            @if($company->contactUser)
                <p class="form-hint">{{ __('messages.company_contact_user') }}: {{ $company->contactUser->name }}</p>
            @endif
            <ul>
                <li>{{ __('messages.phone') }}: {{ $company->phone ?: __('messages.not_provided') }}</li>
                <li>{{ __('messages.email') }}: {{ $company->email ?: __('messages.not_provided') }}</li>
                <li>
                    @if($company->website)
                        <a href="{{ $company->website }}">{{ $company->website }}</a>
                    @else
                        {{ __('messages.website') }}: {{ __('messages.not_provided') }}
                    @endif
                </li>
            </ul>
        </article>

        <article class="company-show-card">
            <h3>{{ __('messages.legal_identifiers') }}</h3>
            <ul>
                @if($company->siren)
                    <li>{{ __('messages.siren') }}: {{ $company->siren }}</li>
                @endif
                @if($company->siret)
                    <li>{{ __('messages.siret') }}: {{ $company->siret }}</li>
                @endif
                @if($company->vat_number)
                    <li>{{ __('messages.vat_number') }}: {{ $company->vat_number }}</li>
                @endif
                @if($company->legalFooterLine())
                    <li>{{ $company->legalFooterLine() }}</li>
                @endif
            </ul>
        </article>

        <article class="company-show-card company-show-card--wide">
            <h3>{{ __('messages.iban') }}</h3>
            @if($company->bank_name)
                <p class="form-hint">{{ __('messages.bank') }}: {{ $company->bank_name }}</p>
            @endif
            @if($company->bank || $company->branch || $company->account || $company->key)
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.bank_code') }}</th>
                            <th>{{ __('messages.branch_code') }}</th>
                            <th>{{ __('messages.account_number') }}</th>
                            <th>{{ __('messages.key') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $company->bank ?: '—' }}</td>
                            <td>{{ $company->branch ?: '—' }}</td>
                            <td>{{ $company->account ?: '—' }}</td>
                            <td>{{ $company->key ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
            <ul>
                <li>{{ __('messages.account_holder') }}: {{ $company->iban_name ?: __('messages.not_provided') }}</li>
                <li>{{ __('messages.iban_code') }}: {{ $company->iban ?: __('messages.not_provided') }}</li>
                <li>{{ __('messages.bic_code') }}: {{ $company->bic ?: __('messages.not_provided') }}</li>
            </ul>
        </article>
    </section>
</x-app-layout>
