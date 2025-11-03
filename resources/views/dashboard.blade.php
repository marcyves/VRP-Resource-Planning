<x-app-layout>
    <x-slot name="header">
        <div class="metrics">
        <ul class="metrics-row">
            <li class="card glass-background">
                <a href="{{ route('invoice.index') }}" class="card-content-text">
                <h2>{{ $bills_count }} Factures</h2>
                <table>
                    <tr><td>{{ __('messages.total_gain')}}</td><td> @moneyVAT($bills_amount)</td></tr>
                    <tr><td>{{ __('messages.total_payed')}}</td><td> @moneyVAT($bills_payed_amount)</td></tr>
                    @php $total_balance = $bills_amount-$bills_payed_amount @endphp
                    <tr><td>{{ __('messages.total_balance')}}</td><td> @moneyVAT($total_balance)</td></tr>
                    @php $not_planned = $bills_amount-$total_planned @endphp
                    <tr><td>{{__('messages.not_planned')}}</td><td>  @moneyVAT($not_planned)</td></tr>
                </table>
            </a>
            </li>

            <li class="card glass-background">
                <div class="card-content-text">
                <h2>Ecoles</h2>
                <ul>
                    <li>Nombre: WIP</li>
                </ul>
                </div>
            </li>

            <li class="card glass-background">
                <div class="card-content-text">
                <h2>Cours</h2>
                <ul>
                    <li>Nombre: WIP</li>
                </ul>
                </li>
                </div>
            </li> 
        </ul>
    </div>


        <div class="metrics glass-background">
            <h2>Planning @moneyVAT($total_planned)</h2>
            <div class="metrics-row">
                <div class="histogram">
                    @foreach ($amounts_planned as $amount)
                        <div class="histogram-bar" style="height: {{($amount*200)/$total_planned}}%;">
                            <span class="histogram-label">@moneyVAT($amount,0)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <div class="metrics glass-background">
            <h2>Factures @moneyVAT($bills_amount)</h2>
        <div class="metrics-row">
        <div class="histogram">
            @foreach ($amounts as $amount)
            <div class="histogram-bar" style="height: {{($amount*200)/$bills_amount}}%;">
                <span class="histogram-label">
             @moneyVAT($amount,0)
                </span>
             </div>
           
            @endforeach
        </div>
        </div>
    </div>                  

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
