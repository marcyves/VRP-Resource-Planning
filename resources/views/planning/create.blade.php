<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Planification: ') . $date }}
        </h2>
    </x-slot>

    <section  class="section-box">

        <form action="{{route('planning.store')}}" method="post">
            @csrf
            <input type="hidden" name="date" value="{{$date}}">
            <input type="hidden" name="course" value="{{$course->id}}">
            <input type="hidden" name="session_length" value="{{$session_length}}">
            <select name="group" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-40 mb-2">
                <option value="0" selected>New group below</option>
                @foreach ($groups as $group)
                    @if($group->sessions > $group->plannings->count())
                       <option value="{{$group->id}}">{{$group->name}}</option>
                    @endif
                @endforeach
            </select>
            <select name="hour" class="rounded-md py-0 pl-2 pr-8 w-14">
                @php
                    for($h=8;$h<20;$h++)
                    {
                @endphp
                        <option value="{{$h}}">{{$h}}</option>
                @php
                    }
                @endphp
            </select>
            <select name="minutes" class="rounded-md py-0 pl-2 pr-8 w-14">
                @php
                    for($m=0;$m<60;$m+=5)
                    {
                @endphp
                        <option value="{{$m}}">{{$m}}</option>
                @php
                    }
                @endphp
            </select>
            <br class="my-4">
            <x-form-group-create course_id=$course_id />
            <br class="my-4">
            <x-primary-button>Plan</x-primary-button>
        </form>

        
    </section>
</x-app-layout>