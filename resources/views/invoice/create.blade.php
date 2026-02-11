<x-app-layout>
    @push('styles')
    @vite(['resources/css/bills.css', 'resources/css/cards.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.invoice_create') }}</h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section class="bills-container glass-background">
        <form action="{{route('invoice.store')}}" method="post" class="group-form glass-background-solid">
            @csrf

            <div class="form-group mb-4">
                <span class="form-label">{{ __('messages.invoice_id') }}: {{ $invoice_id }} | Date: {{ $bill_date }}</span>
            </div>

            <div class="card-grid">
                <div class="card glass-background">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">{{$company->name}}</h2>
                        <ul class="flex-list">
                            <li>{{$company->address}}</li>
                            <li>{{$company->zip }} {{$company->city}}</li>
                            <li>{{$company->country}}</li>
                        </ul>
                        <h3 class="card-subtitle mt-4">Contact</h3>
                        <ul class="flex-list">
                            <li>{{$company->phone}}</li>
                            <li>{{$company->email}}</li>
                            <li><a href="{{$company->website}}" class="nav-link">{{$company->website}}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card glass-background">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">Banque: {{$company->bank_name}}</h2>
                        <table class="simple-table mt-2">
                            <thead>
                                <tr>
                                    <th>Code banque</th>
                                    <th>Code guichet</th>
                                    <th>Numéro de compte</th>
                                    <th>Clé</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{$company->bank}}</td>
                                    <td>{{$company->branch}}</td>
                                    <td>{{$company->account}}</td>
                                    <td>{{$company->key}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <ul class="flex-list mt-4">
                            <li>Titulaire du compte: {{$company->iban_name}}</li>
                            <li>Code IBAN: {{$company->iban}}</li>
                            <li>Code BIC/SWIFT: {{$company->bic}}</li>
                        </ul>
                    </div>
                </div>

                <div class="card glass-background">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">Client</h2>
                        <ul class="flex-list">
                            <li>{{$school->name}}</li>
                            <li>{{$school->address}}</li>
                            <li>{{$school->zip }} {{$school->city}}</li>
                            <li>{{$school->country}}</li>
                            <li class="mt-2">Contact: {{$school->contact}}</li>
                            <li>Email: {{$school->email}}</li>
                            <li>Téléphone: {{$school->phone}}</li>
                        </ul>
                    </div>
                </div>

                <div class="card glass-background">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">Items</h2>
                        <ul class="flex-list item-list">
                            @foreach($items as $item)
                            <li class="item-row">
                                @if($item[4] == "T")
                                <div class="item-title">
                                    <strong>{{htmlspecialchars($item[0])}}</strong>
                                    <span class="item-details">Rate: @money($item[2])€ Hours : @money($item[3])</span>
                                </div>
                                @else
                                <div class="item-line">
                                    {{htmlspecialchars($item[0])}}
                                    <span class="item-details">
                                        @if(is_numeric($item[2]))
                                        {{$item[1]}} @money($item[2])€ {{$item[3]}}h
                                        @else
                                        {{$item[1]}} {{$item[2]}} {{$item[3]}}
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </li>
                            @endforeach
                            <li class="item-total mt-4 pt-4 border-t">
                                <strong>Total : @money($total_amount*1.2) €</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-group mt-6">
                <input type="hidden" name="invoice_id" id="name" value="{{$bill_number}}">
                <input type="hidden" name="school_id" id="school_id" value="{{$school->id}}">
                <input type="hidden" name="bill_date" id="bill_date" value="{{$bill_date}}">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount">Montant (laisser vide pour calcul auto)</x-input-label>
                        <x-text-input type="number" step="0.01" name="amount" id="amount" value="{{$total_amount > 0 ? $total_amount * 1.2 : ''}}" placeholder="Ex: 500.00" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <x-input-label for="month">Mois</x-input-label>
                        <x-text-input type="number" name="month" id="month" value="{{$month}}" min="1" max="12" />
                    </div>
                    <div>
                        <x-input-label for="year">Année</x-input-label>
                        <x-text-input type="number" name="year" id="year" value="{{$year}}" />
                    </div>
                </div>
                <x-input-label for="description">{{ __('messages.description') }}</x-input-label>
                <x-text-input type="text" name="description" id="description" placeholder="{{ __('messages.description') }}" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.bill_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>