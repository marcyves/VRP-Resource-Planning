@props(['years', 'months', 'current_year', 'current_month', 'route'])
<!-- (A) PERIOD SELECTOR -->
<div id="calPeriod">
    <form class="inline-flex" action="{{route($route.".index")}}" method="get">
        @csrf
        <button type="submit" formaction="{{route($route.".previous")}}" class="cool-box icon"><</button>
        <select id="current_month" name="current_month" onchange="this.form.submit()" class="cool-box">
            @foreach ($months as $index => $month)
                <option value="{{$index}}" @if($index==$current_month-1) selected @endif>{{$month}}</option>                    
            @endforeach
        </select>
        <button type="submit" formaction="{{route($route.".next")}}" class="cool-box icon">></button>
    </form>
</div>