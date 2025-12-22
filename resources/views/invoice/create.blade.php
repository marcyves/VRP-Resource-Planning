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
            <x-input-label class="my-4">{{ __('messages.invoice_id') }}: {{$invoice_id}} Date: {{$bill_date}}</x-input-label> 
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
                <li class="card">
                    <div class="card-content-text">
                    <ul>
                        @foreach($items as $item)
                            <li>
                                @if($item[4] == "T")
                                    <strong>{{htmlspecialchars($item[0])}}</strong>
                                    <br>
                                    Rate:  @money($item[2])€ Hours : @money($item[3])
                                @else   
                                    {{htmlspecialchars($item[0])}}
                                    @if(is_numeric($item[2]))
                                        {{$item[1]}} @money($item[2])€ {{$item[3]}}h
                                    @else
                                        {{$item[1]}} {{$item[2]}} {{$item[3]}}
                                    @endif
                                @endif
                            </li>
                        @endforeach
                        <li>
                            <strong>Total : @money($total_amount*1.2) €</strong>
                        </li>
                    </ul>
                    </div>
                </li>
            </ul>
            <input type="hidden" name="invoice_id" id="name" value="{{$bill_number}}">
            <input type="hidden" name="school_id" id="school_id" value="{{$school->id}}">
            <input type="hidden" name="month" id="month" value="{{$month}}">
            <input type="hidden" name="year" id="year" value="{{$year}}">
            <input type="hidden" name="bill_date" id="bill_date" value="{{$bill_date}}">
            <x-text-input type="text" name="description" id="description" size="60" placeholder="{{ __('messages.description') }}"/>
            <div>
            <x-primary-button>{{ __('messages.bill_create') }}</x-primary-button>
            </div>
        </form>
    </section>
    @endif

</x-app-layout>