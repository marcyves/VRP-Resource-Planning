<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="header-title grow py-2">
                {{ __('messages.my_company') }}
            </h2>
            @if(Auth::user()->isAdmin() || Auth::user()->isEditor())
            <form action="{{ route('company.edit') }}" method="get">
                <x-button-edit />
            </form>
            @endif
        </div>
    </x-slot>

    <section>
        <ul class="list">
            <li class="card">
                <div class="card-content-text">
                    <h2>{{$company->name}}</h2>
                    <ul>
                        <li>{{$company->address}}</li>
                        <li>{{$company->zip }} {{$company->city}}</li>
                        <li>{{$company->country}}</li>
                    </ul>
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                    <h2>{{ __('messages.contact') }}</h2>
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
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                    <h2>{{ __('messages.legal_identifiers') }}</h2>
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
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                    <h2>{{ __('messages.iban') }}</h2>
                    @if($company->bank_name)
                    <p>{{ __('messages.bank') }}: {{ $company->bank_name }}</p>
                    @endif
                    @if($company->bank || $company->branch || $company->account || $company->key)
                    <table class="simple-table mt-2">
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
                    <ul class="mt-2">
                        <li>{{ __('messages.account_holder') }}: {{ $company->iban_name ?: __('messages.not_provided') }}</li>
                        <li>{{ __('messages.iban_code') }}: {{ $company->iban ?: __('messages.not_provided') }}</li>
                        <li>{{ __('messages.bic_code') }}: {{ $company->bic ?: __('messages.not_provided') }}</li>
                    </ul>
                </div>
            </li>
        </ul>
    </section>
</x-app-layout>