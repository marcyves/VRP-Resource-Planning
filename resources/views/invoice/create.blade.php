<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.invoice_create') }}</h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section class="bills-container">
        <form action="{{route('invoice.store')}}" method="post" class="group-form">
            @csrf

            <div class="form-group mb-4">
                <span class="form-label">{{ __('messages.invoice_id') }}: {{ $invoice_id }} | {{ __('messages.date') }}: {{ $bill_date }}</span>
            </div>

            <div class="card-grid">
                <div class="card">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">{{$company->name}}</h2>
                        <ul class="flex-list">
                            <li>{{$company->address}}</li>
                            <li>{{$company->zip }} {{$company->city}}</li>
                            <li>{{$company->country}}</li>
                        </ul>
                        <h3 class="card-subtitle mt-4">{{ __('messages.contact') }}</h3>
                        <ul class="flex-list">
                            <li>{{$company->phone}}</li>
                            <li>{{$company->email}}</li>
                            <li><a href="{{$company->website}}" class="nav-link">{{$company->website}}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">{{ __('messages.bank') }}: {{$company->bank_name}}</h2>
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
                                    <td>{{$company->bank}}</td>
                                    <td>{{$company->branch}}</td>
                                    <td>{{$company->account}}</td>
                                    <td>{{$company->key}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <ul class="flex-list mt-4">
                            <li>{{ __('messages.account_holder') }}: {{$company->iban_name}}</li>
                            <li>{{ __('messages.iban_code') }}: {{$company->iban}}</li>
                            <li>{{ __('messages.bic_code') }}: {{$company->bic}}</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">{{ __('messages.client') }}</h2>
                        <ul class="flex-list">
                            <li>{{$school->name}}</li>
                            <li>{{$school->address}}</li>
                            <li>{{$school->zip }} {{$school->city}}</li>
                            <li>{{$school->country}}</li>
                            <li class="mt-2">{{ __('messages.contact') }}: {{$school->contact}}</li>
                            <li>{{ __('messages.email') }}: {{$school->email}}</li>
                            <li>{{ __('messages.phone') }}: {{$school->phone}}</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content-text">
                        <h2 class="card-subtitle">{{ __('messages.items') }}</h2>
                        <ul class="bill-list">
                            @foreach($items as $item)
                            <li class="item-row">
                                @if($item[4] == "T")
                                <div class="item-title">
                                    <strong>{{htmlspecialchars($item[0])}}</strong>
                                    <span class="item-details">{{ __('messages.rate') }}: @money($item[2])€ {{ __('messages.hours') }}: @money($item[3])</span>
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
                                <strong>{{ __('messages.total') }} : @money($total_amount*1.2) €</strong>
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
                        <x-input-label for="amount">{{ __('messages.amount_including_tax_auto') }}</x-input-label>
                        <x-text-input type="number" step="0.01" name="amount" id="amount" value="{{$total_amount > 0 ? $total_amount * 1.2 : ''}}" placeholder="{{ __('messages.amount_example') }}" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <x-input-label for="month">{{ __('messages.billing_day') }}</x-input-label>
                        <x-text-input type="text" name="bill_date" id="bill_date" value="{{$bill_date}}" min="1" max="12" />
                    </div>
                    <div>
                        <x-input-label for="month">{{ __('messages.month') }}</x-input-label>
                        <x-text-input type="number" name="month" id="month" value="{{$month}}" min="1" max="12" />
                    </div>
                    <div>
                        <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
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