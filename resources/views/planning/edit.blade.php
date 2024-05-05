<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Planification') }}
        </h2>
    </x-slot>

    @php
        $begin_date = explode(" ", $planning->begin)[0];
        $begin_day = explode("-", $begin_date)[2];
        $begin_month = explode("-", $begin_date)[1];
        $begin_year = explode("-", $begin_date)[0];

        $begin_time = explode(" ", $planning->begin)[1];
        $begin_hour = explode(":", $begin_time)[0];
        $begin_minutes = explode(":", $begin_time)[1];

        $end_time = explode(" ", $planning->end)[1];
        $end_hour = explode(":", $end_time)[0];
        $end_minutes = explode(":", $end_time)[1];
    @endphp

    <section  class="nice-page">
        <div class="grid grid-flow-row-dense grid-cols-2 grid-rows-2">
            <div>
                <form action="{{route('planning.update', $planning->id)}}" method="post">
                    @csrf
                    @method('put')
                    <select name="group_id" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-40 mb-2">
                        @foreach ($groups as $group)
                        <option value="{{$group->id}}"
                            @if($group->id == $planning->group_id)
                            selected
                            @endif
                            >{{$group->name}}</option>
                        @endforeach
                    </select>
            </div>
            <div>
                <label for="day">Day:</label>           
                <select id="day" name="day" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-20 mb-2">
                    @for($d=1;$d<32;$d++)
                    <option value="{{$d}}"
                    @if($d==$begin_day) selected @endif>{{$d}}</option>
                    @endfor
                </select>
                <select name="month" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-20 mb-2">
                    @for($m=1;$m<13;$m++)
                    <option value="{{$m}}"
                    @if($m==$begin_month) selected @endif>{{$m}}</option>
                    @endfor
                </select>
            </div>
            <div>
            Begin: 
            <select name="hour" class="rounded-md py-0 pl-2 pr-8 w-20">
                @php
                    for($h=8;$h<20;$h++)
                    {
                @endphp
                        <option value="{{$h}}"
                        @if($h==$begin_hour) selected @endif>{{$h}}</option>
                @php
                    }
                @endphp
            </select>
            <input type="hidden" name="year" value="{{$begin_year}}">
            <select name="minutes" class="rounded-md py-0 pl-2 pr-8 w-20">
                @php
                    for($m=0;$m<60;$m+=5)
                    {
                @endphp
                        <option value="{{$m}}"
                        @if($m==$begin_minutes) selected @endif>{{$m}}</option>
                @php
                    }
                @endphp
            </select>
            </div>
            <div>
            End: 
            <select name="end_hour" class="rounded-md py-0 pl-2 pr-8 w-20">
                @php
                    for($h=8;$h<20;$h++)
                    {
                @endphp
                        <option value="{{$h}}"
                        @if($h==$end_hour) selected @endif>{{$h}}</option>
                @php
                    }
                @endphp
            </select>
            <input type="hidden" name="year" value="{{$begin_year}}">
            <select name="end_minutes" class="rounded-md py-0 pl-2 pr-8 w-20">
                @php
                    for($m=0;$m<60;$m+=5)
                    {
                @endphp
                        <option value="{{$m}}"
                        @if($m==$end_minutes) selected @endif>{{$m}}</option>
                @php
                    }
                @endphp
            </select>
            </div>
            </div>
            <br class="my-4">
            <x-primary-button>Plan</x-primary-button>
        </form>
    </section>

</x-app-layout>