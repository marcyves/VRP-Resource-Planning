<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Planification') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
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
    </x-nice-box>

</x-app-layout>