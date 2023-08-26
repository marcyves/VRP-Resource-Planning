<div class="calCell px-2 pt-1 bg-blue-200 flex-col justify-stretch">

    <div class="bg-white px-2 py-1 border border-blue-400 rounded-md mb-1 text-blue-400 text-center">
        {{$day}}
    </div>

    @foreach ($planning as $event)
        @php
            $begin_date = explode(" ", $event->begin)[0];
            $begin_time  = substr(explode(" ", $event->begin)[1], 0, 5);
            $begin_day = explode("-", $begin_date)[2];
            $end_date = explode(" ", $event->end)[0];
            $end_day = explode("-", $end_date)[2];
            $tmp = "";
            if ((int)$begin_day == $i){
        @endphp
            <div class="text-gray-400 bg-white px-2 py-1 border border-blue-400 rounded-md mb-1">
                {{$begin_time}}: {{$event->short_name}} ({{$event->group_short_name}})
            </div>
        @php
            }
        @endphp
    @endforeach

    <form action="{{ route('planning.insert', $day)}}" method="post" class="bg-gray-200 px-2 py-1 border border-blue-400 rounded-md mb-4">
        @csrf
        <input type="hidden" name="day" value={{$day}}>
        <input type="hidden" name="month" value={{$month}}>
        <input type="hidden" name="year" value={{$year}}>
        <select name="course" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-28 mb-2">
            @foreach ($courses as $course)
            <option value="{{$course->id}}">{{$course->name}}</option>                            
            @endforeach
        </select>
        <br>
        <input 
        class="inline-flex items-center p-2 my-2 text-sm border border-gray-400 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
        type="submit" value="Set">
    </form>

</div>