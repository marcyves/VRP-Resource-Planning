<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Planification: ') . $date }}
        </h2>
    </x-slot>

    <section  class="nice-page">

        <x-nice-title color="grey-200" title="Groups">
            <a
            class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('group.new', $course->id)}}">New Group</a>
        </x-nice-title>

        <form action="{{route('planning.store')}}" method="post">
            @csrf
            <input type="hidden" name="date" value="{{$date}}">
            <input type="hidden" name="course" value="{{$course->id}}">
            <input type="hidden" name="session_length" value="{{$session_length}}">
            <select name="group" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-40 mb-2">
                @foreach ($groups as $group)
                <option value="{{$group->id}}">{{$group->name}}</option>
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
            <x-primary-button>Plan</x-primary-button>
        </form>
    </section>
</x-app-layout>