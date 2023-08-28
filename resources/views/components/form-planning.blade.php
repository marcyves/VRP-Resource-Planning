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
            if ((int)$begin_day == $day){
        @endphp
        <div class="text-gray-400 block bg-white px-2 py-1 border border-blue-400 rounded-md mb-1">
            <div class="flex flex-row items-center justify-between">
                <div>
                    {{$begin_time}}: {{$event->short_name}} ({{$event->group_short_name}})
                </div>
                <div class="w-full md:w-auto flex flex-col space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <a href="{{route('planning.edit',$event->id)}}">
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                    </a>
                </div>
                <div class="w-full md:w-auto flex flex-col space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <form action="{{route('planning.delete',$event->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>                                  
                        </button>
                    </form>
                </div>
            </div>
        </div>
            
        @php
            }
        @endphp
    @endforeach

    <form action="{{ route('planning.insert', $day)}}" method="post" class="mb-4">
        @csrf
        <input type="hidden" name="day" value={{$day}}>
        <input type="hidden" name="month" value={{$month}}>
        <input type="hidden" name="year" value={{$year}}>
        <select name="course" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-28 mb-2"  onchange="this.form.submit()">
            @foreach ($courses as $course)
            <option value="{{$course->id}}">{{$course->name}}</option>                            
            @endforeach
        </select>
    </form>

</div>