<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Planning') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

        <!-- (A) PERIOD SELECTOR -->
        @php
            $months = array(); //generate all the month names according to the current locale
            for ($n = 0, $t = (4) * 86400; $n < 12; $n++, $t+=86400*30) //January 5, 1970
                $months[$n] = ucfirst(date('F', $t));

            $weekdays = array(); //generate all the day names according to the current locale
            for ($n = 0, $t = (4) * 86400; $n < 7; $n++, $t+=86400) //January 5, 1970 was a Monday
                $weekdays[$n] = ucfirst(date('l', $t));
    
            $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
            $numDays = date('t', $firstDay);
            //$monthName = getMonthName($month);
            $startDay = date('N', $firstDay);

            $day = 1;
        @endphp
        <div id="calPeriod">
            <form class="inline-flex" id="calYear" action="{{route('planning.period')}}" method="post">
                @csrf
                <select id="current_year" name="current_year" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
                    @foreach ($years as $year)
                        <option value="{{$year->year}}" @if($current_year == $year->year)selected @endif>{{$year->year}}</option>
                    @endforeach                
                </select>
            </form>

            <form class="inline-flex" action="{{route('planning.period')}}" method="post">
                @csrf
                <select id="calMonth" name="current_month" onchange="this.form.submit()">
                    @foreach ($months as $index => $month)
                        <option value="{{$index}}" @if($index==$current_month-1) selected @endif>{{$month}}</option>                    
                    @endforeach
                </select>
            </form>
          </div>
      
          <!-- (B) CALENDAR -->
          <div id="calWrap">
            <div class="calHead">
                @foreach ($weekdays as $weekday)
                <div class="calCell">{{$weekday}}</div>                    
                @endforeach
            </div>

          <div class="calBody">
            <div class="calRow">
                <!-- Afficher les jours avant le premier jour du mois -->
                @for ($i = 1; $i < $startDay; $i++)
                    <div class="calCell px-2 pt-1 bg-gray-100 flex-col justify-stretch border border-black m-1">
                    </div>
                @endfor
                <!-- Afficher les jours du mois -->
                @for ($i = $startDay; $i <= 7; $i++)
                        <x-form-planning :courses=$courses :planning=$planning i={{$i}} :day=$day month={{$current_month}} year={{$current_year}}/>
                        @php
                        $day++;
                        @endphp                    
                @endfor
            </div>
        
            <!-- Afficher le reste des jours du mois -->
            @while ($day <= $numDays)
                <div class="calRow">
                @for ($i = 1; $i <= 7 && $day <= $numDays; $i++)
                    <x-form-planning :courses=$courses :planning=$planning i={{$i}} :day=$day month={{$current_month}} year={{$current_year}}/>
                    @php
                    $day++;
                    @endphp                
                @endfor
            </div>
            @endwhile
        </div>
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                Time worked = {{$monthly_hours}} hours
            </div>
            <div class="mx-4">
                Monthly gain = {{number_format($monthly_gain,2)}} €
            </div>
            <div class="mx-4">
                Average Rate = @if ($monthly_hours == 0) 0 @else {{number_format($monthly_gain/$monthly_hours,2)}} @endif€
            </div>
            <div class="justify-end">
                <form action="{{route('planning.billing')}}" method="post">
                    @csrf
                    <input type="hidden" name="year" value="{{$current_year}}">
                    <input type="hidden" name="month" value="{{$current_month}}">
                    <button class="border border-gray-400 bg-white rounded-md px-4 mr-4">
                        Billing
                    </button>
                </form>
            </div>
        </div>
        </x-nice-box>

</x-app-layout>