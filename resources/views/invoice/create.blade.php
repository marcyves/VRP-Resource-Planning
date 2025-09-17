<x-app-layout>
    <x-slot name="header">
            <h2>
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('invoice.store')}}" method="post" class="bills-form">
            @csrf
            <x-input-label class="my-4">{{ __('messages.bill_id') }}: {{$bill_id}}</x-input-label> 
            <ul class="list">
                <li class="card">
                    <div class="card-content-text">
                        <h2>{{$company->name}}</h2>
                        <ul>
                            <li>{{$company->address}}</li>
                            <li>{{$company->zip }} {{$company->city}}</li>
                            <li>{{$company->country}}</li>
                        </ul>
                        <h2>Contact</h2>
                        <ul>
                            <li>{{$company->phone}}</li>
                            <li>{{$company->email}}</li>
                            <li><a href={{$company->website}}>{{$company->website}}</a></li>
                        </ul>
                    </div>
                </li>
                <li class="card">
                    <div class="card-content-text">
                    <h2>Banque: {{$company->bank_name}}</h2>
                    <table>
                        <tr>
                            <td>Code banque</td>
                            <td>Code guichet</td>
                            <td>Numéro de compte</td>
                            <td>Clé</td>
                        </tr>
                        <tr>
                            <td>{{$company->bank}}</td> 
                            <td>{{$company->branch}}</td> 
                            <td>{{$company->account}}</td> 
                            <td>{{$company->key}}</td> 
                        </tr>
                    </table>
                    <ul>
                        <li>Titulaire du compte: {{$company->iban_name}}</li>
                        <li>Code IBAN: {{$company->iban}}</li>
                        <li>Code BIC/SWIFT: {{$company->bic}}</li>
                        <li>{{$company->iban}}</li>
                        <li>{{$company->bank}} {{$company->bank}}</li>
                    </ul>
                    </div>
                                    </li>
                <li class="card">
                    <div class="card-content-text">
                    <h2>Client</h2>
                    <ul>
                        <li>{{$school->name}}</li>
                        <li>{{$school->address}}</li>
                        <li>{{$school->zip }} {{$school->city}}</li>
                        <li>{{$school->country}}</li>
                        <li>Contact: {{$school->contact}}</li>
                        <li>Email: {{$school->email}}</li>
                        <li>Téléphone: {{$school->phone}}</li>
                    </ul>
                    </div>
                </li>
            </ul>
            <input type="hidden" name="id" id="name" value="{{$bill_number}}">
            <input type="hidden" name="school_id" id="school_id" value="{{$school->id}}">
            <x-text-input type="text" name="description" id="description" size="60" placeholder="{{ __('messages.description') }}"/>
            <div>
            <x-text-input class="my-2" type="text" name="amount" id="amount" size="20" placeholder="{{ __('messages.amount') }}"/>
            <x-primary-button>{{ __('messages.bill_create') }}</x-primary-button>
            </div>
        </form>
    </section>
    @endif

</x-app-layout>