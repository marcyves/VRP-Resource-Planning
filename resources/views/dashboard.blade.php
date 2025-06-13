<x-app-layout>
    <x-slot name="header">
            <ul class="list">
                <li class="card">
                    <a href="{{ route('bill.index') }}" class="card-content-text">
                    <h2>{{ $bills_count }} Factures</h2>
                    <ul>
                        <li>{{ __('messages.total_gain')}}: @money($bills_amount) €</li>
                        <li>{{ __('messages.total_payed')}}: @money($bills_payed_amount) €</li>
                        <li>{{ __('messages.total_balance')}}: @money($bills_amount-$bills_payed_amount) €</li>
                    </ul>
                </a>
                </li>
    
                <li class="card">
                    <div class="card-content-text">
                    <h2>Ecoles</h2>
                    <ul>
                        <li>Nombre: WIP</li>
                    </ul>
                    </div>
                </li>
    
                <li class="card">
                    <div class="card-content-text">
                    <h2>Cours</h2>
                    <ul>
                        <li>Nombre: WIP</li>
                    </ul>
                    </li>
                    </div>
                </li> 
            </ul>
    </x-slot>

    <section>
        @php
            $gross_total_time = 0;
            $gross_total_budget = 0;
            $total_time = 0;
            $total_budget = 0;
        @endphp
        @foreach ($schools as $school)
            @if($school->courses->count() > 0)
                @php
                $total_time = 0;
                $total_budget = 0;
                $school_id = $school->id;
                $school_name = $school->name;
                @endphp
                <article class="school-box">
                    <x-school-header :school_name=$school_name :school_id=$school_id />
                    @php
                    $school_courses = $courses->where('school_name', $school_name);
                    @endphp
                    <x-course-table :courses=$school_courses :school_name=$school_name :school_id=$school_id/>
                    @php
                    $gross_total_time += $total_time;
                    $gross_total_budget += $total_budget;
                    @endphp
                </article>
            @endif
        @endforeach

        @if ($gross_total_time > 0)
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-600 uppercase bg-gray-50">
                <tr>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col">{{ __('messages.total_time') }}</th>
                    <th scope="col">{{ $gross_total_time }}</th>
                    <th scope="col">{{ __('messages.total_gain') }}</th>
                    <th scope="col">@money($gross_total_budget)</th>
                    <th scope="col">{{ __('messages.hour_rate') }}</th>
                    <th scope="col">@money($gross_total_budget / $gross_total_time)</th>
                    <th scope="col"></th>
                </tr>
            </thead>
        </table>
    @endif

    </section>

</x-app-layout>
