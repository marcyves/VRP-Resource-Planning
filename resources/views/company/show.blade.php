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
            <h3>{{ __('messages.billing_bank_account') }}</h3>
            <x-company-billing-details :account="$company->billingBankAccount" />
            @if (Auth::user()->isAdmin() || Auth::user()->isEditor())
                <p class="company-billing-actions">
                    <a class="btn btn-secondary btn--compact" href="{{ route('company.edit') }}#billing_bank_account_id">{{ __('messages.edit') }}</a>
                    <a class="btn btn-secondary btn--compact" href="{{ route('treasury.bank.index') }}">{{ __('messages.manage_bank_accounts') }}</a>
                </p>
            @endif
        </article>
    </section>
</x-app-layout>
