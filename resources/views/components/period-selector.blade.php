@props(['years', 'months', 'current_year', 'current_month', 'route'])
<!-- (A) PERIOD SELECTOR -->
<div id="calPeriod">
    <form class="inline-flex" id="calYear" action="{{route($route)}}" method="get">
        @csrf
        <select id="current_year" name="current_year" class="rounded-md mt-4 py-0 pl-2 pr-8" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{$year->year}}" @if($current_year == $year->year)selected @endif>{{$year->year}}</option>
            @endforeach                
        </select>
    </form>

    <form class="inline-flex" action="{{route('planning.billing')}}" method="get">
        @csrf
        <select id="current_month" name="current_month" onchange="this.form.submit()">
            @foreach ($months as $index => $month)
                <option value="{{$index}}" @if($index==$current_month-1) selected @endif>{{$month}}</option>                    
            @endforeach
        </select>
    </form>
</div>