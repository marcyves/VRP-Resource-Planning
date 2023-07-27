<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <ul>
        @foreach ($groups as $group)
            <li>{{$group->name}}</li>            
        @endforeach
        </ul>
    </x-nice-box>
</x-app-layout>