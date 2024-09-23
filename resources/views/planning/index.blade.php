<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.planning') }} @monthName($current_month) {{$current_year}}
        </h2>
    </x-slot>

    <section  class="nice-page">

        <!-- (A) PERIOD SELECTOR -->
        @php    
            $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
            $numDays = date('t', $firstDay);
            //$monthName = getMonthName($month);
            $startDay = date('N', $firstDay);
            $day = 1;
        @endphp
        <div id="calPeriod">
        <x-period-selector :years=$years :months=$months current_year={{$current_year}} current_month={{$current_month}} route="planning"/>
        <x-form-select-planning :mode=$mode :schools=$schools :courses=$courses :planning=$planning day={{$current_day}} month={{$current_month}} year={{$current_year}}/>
        </div>
          <!-- (B) CALENDAR -->
          <div id="calWrap">
            <div class="calHead">
                @foreach ($weekdays as $weekday)
                <div class="calCell">{{__($weekday)}}</div>                    
                @endforeach
            </div>

          <div class="calBody">
            <div class="calRow">
                <!-- Afficher les jours avant le premier jour du mois -->
                @for ($i = 1; $i < $startDay; $i++)
                    <div class="calBlank calCell">
                    </div>
                @endfor
                <!-- Afficher les jours du mois -->
                @for ($i = $startDay; $i <= 7; $i++)
                        <x-form-planning :mode=$mode :schools=$schools :courses=$courses :planning=$planning i={{$i}} :day=$day month={{$current_month}} year={{$current_year}}/>
                        @php
                        $day++;
                        @endphp                    
                @endfor
            </div>
        
            <!-- Afficher le reste des jours du mois -->
            @while ($day <= $numDays)
                <div class="calRow">
                @for ($i = 1; $i <= 7 && $day <= $numDays; $i++)
                    <x-form-planning :mode=$mode :schools=$schools :courses=$courses :planning=$planning i={{$i}} :day=$day month={{$current_month}} year={{$current_year}}/>
                    @php
                    $day++;
                    @endphp                
                @endfor
            </div>
            @endwhile

        </div>
        <div class="flex flex-row justify-between font-semibold text-gray-600 mt-4 py-4 bg-gray-200">
            <div class="mx-4">
            {{ __('messages.time_worked') }} = {{$monthly_hours}} {{ __('messages.hours') }}
            </div>
            <div class="mx-4">
            {{ __('messages.monthly_gain') }} = {{number_format($monthly_gain,2)}} €
            </div>
            <div class="mx-4">
            {{ __('messages.hour_rate') }} = @if ($monthly_hours == 0) 0 @else {{number_format($monthly_gain/$monthly_hours,2)}} @endif€
            </div>
        </div>
        </section>

</x-app-layout>