<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <x-nice-title color="grey-200" title="Planning">
        </x-nice-title>
        <ul>
        @foreach ($planning as $event)
        <li class="mx-4 my-2">
            {{$event->begin}} ({{$event->end}}) 
        </li> 
        @endforeach
        </ul>
  
    </x-nice-box>

</x-app-layout>