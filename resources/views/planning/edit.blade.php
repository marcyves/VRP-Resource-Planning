<x-app-layout>
    <x-slot name="header">
        <h2>
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

    <section class="glass-background">
        <form action="{{route('planning.update', $planning->id)}}" method="post">
            @csrf
            @method('put')

            <div class="planning-row">
                <label for="day">Date</label>
                <select id="day" name="day">
                    @for($d=1;$d<32;$d++)
                        <option value="{{$d}}"
                        @if($d==$begin_day) selected @endif>{{$d}}</option>
                        @endfor
                </select>
                <select name="month">
                    @for($m=1;$m<13;$m++)
                        <option value="{{$m}}"
                        @if($m==$begin_month) selected @endif>{{$m}}</option>
                        @endfor
                </select>
                <label for="begin">Begin</label>
                <select name="hour" id="begin">
                    @php
                    for($h=8;$h<22;$h++)
                        {
                        @endphp
                        <option value="{{$h}}"
                        @if($h==$begin_hour) selected @endif>{{$h}}</option>
                        @php
                        }
                        @endphp
                </select>
                <input type="hidden" name="year" value="{{$begin_year}}">
                <select name="minutes">
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
                <label for="end">End</label>
                <select name="end_hour" id="end">
                    @php
                    for($h=8;$h<22;$h++)
                        {
                        @endphp
                        <option value="{{$h}}"
                        @if($h==$end_hour) selected @endif>{{$h}}</option>
                        @php
                        }
                        @endphp
                </select>
                <select name="end_minutes">
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
                <input type="hidden" name="year" value="{{$begin_year}}">
                <label for="rate">Billable rate</label>
                <input type="text" id="rate" value="{{$planning->billable_rate}}" name="billable_rate">
            </div>
            <div class="planning-row">
                <label>Group</label>
                <select name="group_id">
                    @foreach ($groups as $group)
                    <option value="{{$group->id}}"
                        @if($group->id == $planning->group_id)
                        selected
                        @endif
                        >{{$group->id}} {{$group->name}}
                    </option>
                    @endforeach
                </select>
                <label>Course</label>
                <select name="course_id">
                    @foreach ($courses as $course)
                    <option value="{{$course->id}}"
                        @if($course->id == $planning->course_id)
                        selected
                        @endif
                        >{{$course->name}}
                    </option>
                    @endforeach
                </select>
            </div>
            <br class="my-4">
            <x-button-primary>Plan</x-button-primary>
        </form>
    </section>

</x-app-layout>