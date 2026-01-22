<x-app-layout>
    @push('styles')
    @vite(['resources/css/plannings.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('Group Planification') }}</h2>
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
        <form action="{{route('planning.update', $planning->id)}}" method="post" class="group-form glass-background-solid">
            @csrf
            @method('put')

            <div class="form-group">
                <label for="day" class="form-label">Date</label>
                <div class="nav-form">
                    <select id="day" name="day" class="form-input">
                        @for($d=1;$d<32;$d++)
                            <option value="{{$d}}" @if($d==$begin_day) selected @endif>{{$d}}</option>
                            @endfor
                    </select>
                    <select name="month" class="form-input">
                        @for($m=1;$m<13;$m++)
                            <option value="{{$m}}" @if($m==$begin_month) selected @endif>{{$m}}</option>
                            @endfor
                    </select>
                    <input type="hidden" name="year" value="{{$begin_year}}">
                </div>
            </div>

            <div class="form-group">
                <label for="begin" class="form-label">Begin</label>
                <div class="nav-form">
                    <select name="hour" id="begin" class="form-input">
                        @for($h=8;$h<22;$h++)
                            <option value="{{$h}}" @if($h==$begin_hour) selected @endif>{{$h}}</option>
                            @endfor
                    </select>
                    <select name="minutes" class="form-input">
                        @for($m=0;$m<60;$m+=5)
                            <option value="{{$m}}" @if($m==$begin_minutes) selected @endif>{{$m}}</option>
                            @endfor
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="end" class="form-label">End</label>
                <div class="nav-form">
                    <select name="end_hour" id="end" class="form-input">
                        @for($h=8;$h<22;$h++)
                            <option value="{{$h}}" @if($h==$end_hour) selected @endif>{{$h}}</option>
                            @endfor
                    </select>
                    <select name="end_minutes" class="form-input">
                        @for($m=0;$m<60;$m+=5)
                            <option value="{{$m}}" @if($m==$end_minutes) selected @endif>{{$m}}</option>
                            @endfor
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="rate" class="form-label">Billable rate</label>
                <x-text-input type="text" id="rate" value="{{$planning->billable_rate}}" name="billable_rate" />
            </div>

            <div class="form-group">
                <label class="form-label">Group</label>
                <select name="group_id" class="form-input">
                    @foreach ($groups as $group)
                    <option value="{{$group->id}}" @if($group->id == $planning->group_id) selected @endif>
                        {{$group->id}} {{$group->name}}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-input">
                    @foreach ($courses as $course)
                    <option value="{{$course->id}}" @if($course->id == $planning->course_id) selected @endif>
                        {{$course->name}}
                    </option>
                    @endforeach
                </select>
            </div>

            <x-button-primary>Plan</x-button-primary>
        </form>
    </section>
</x-app-layout>