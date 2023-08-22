<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Planning') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <ul>
        @foreach ($planning as $event)
        <li class="mx-4 my-2">
            {{$event->begin}} ({{$event->end}}) 
        </li> 
        @endforeach
        </ul>

        <!-- (A) PERIOD SELECTOR -->
        @php
            $months = array(); //generate all the day names according to the current locale
            for ($n = 0, $t = (4) * 86400; $n < 12; $n++, $t+=86400*30) //January 5, 1970
                $months[$n] = ucfirst(date('F', $t));

            $weekdays = array(); //generate all the day names according to the current locale
            for ($n = 0, $t = (4) * 86400; $n < 7; $n++, $t+=86400) //January 5, 1970 was a Monday
                $weekdays[$n] = ucfirst(date('l', $t));

            $current_month = now()->format('m')/1;
            $current_index = $current_month - 1;
            $current_year  =  now()->format('Y');
    
            $firstDay = mktime(0, 0, 0, $current_month, 1, $current_year);
            $numDays = date('t', $firstDay);
            //$monthName = getMonthName($month);
            $startDay = date('N', $firstDay);

            $day = 1;
        @endphp
        <div id="calPeriod">
            <select id="calMonth">
                @foreach ($months as $index => $month)
                    <option value="{{$index}}" @if($index==$current_index) selected @endif>{{$month}}</option>                    
                @endforeach
            <input type="number" id="calYear" value="{{$current_year}}">
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
                    <div class="calCell calBlank"></div>
                @endfor
                <!-- Afficher les jours du mois -->
                @for ($i = $startDay; $i <= 7; $i++)
                <div class="calCell">
                        {{$day++}}
                    <x-form-planning :courses=$courses day={{$day-1}}/>
                </div> 
                @endfor
            </div>
        
            <!-- Afficher le reste des jours du mois -->
            @while ($day <= $numDays)
                <div class="calRow">
                @for ($i = 1; $i <= 7 && $day <= $numDays; $i++)
                <div class="calCell">
                    <a href="">
                        {{$day++}}
                        <x-form-planning :courses=$courses day={{$day-1}}/>
                    </a>
                </div> 
                @endfor
            </div>
            @endwhile
        </div>
        </x-nice-box>

</x-app-layout>