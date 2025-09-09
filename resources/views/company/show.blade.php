<x-app-layout>
    <x-slot name="header">
        <div class="flex">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight grow py-2">
                Mon entreprise
            </h2>
        </div>
    </x-slot>

    <section class="section-box">
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
                <h2>IBAN</h2>
                <ul>
                    <li>{{$company->iban_name}}</li>
                    <li>{{$company->bank}} {{$company->branch}} {{$company->account}}</li>
                    <li>BIC {{$company->bic}}</li>
                </ul>
                </div>
            </li>
        </ul>
    </section>
</x-app-layout>