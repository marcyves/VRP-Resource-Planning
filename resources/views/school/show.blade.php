<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

<ul>
    <li>{{$school->id}}</li>
    <li>{{$school->name}}</li>
</ul>
        
    </x-nice-box>
</x-app-layout>