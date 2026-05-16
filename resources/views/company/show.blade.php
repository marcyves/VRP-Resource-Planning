<x-app-layout>
    <x-slot name="header">
        <div class="flex">
            <h2 class="header-title grow py-2">
                {{ __('messages.my_company') }}
            </h2>
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
                    <ul>
                        <li>{{$company->phone}}</li>
                        <li>{{$company->email}}</li>
                        <li><a href={{$company->website}}>{{$company->website}}</a></li>
                    </ul>
                </div>
            </li>
            <li class="card">
                <div class="card-content-text">
                <h2>{{ __('messages.iban') }}</h2>
                <ul>
                    <li>{{ __('messages.account_holder') }}: {{$company->iban_name}}</li>
                    <li>{{ __('messages.iban_code') }}: {{$company->account}}</li>
                    <li>{{ __('messages.bic_code') }}: {{$company->bic}}</li>
                </ul>
                </div>
            </li>
        </ul>
    </section>
</x-app-layout>