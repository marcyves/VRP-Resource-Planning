<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schools List') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

<ul>
    @foreach ($schools as $school)
    <li class="mb-2 text-sm font-medium text-blue-800 hover:text-gray-800 rounded-lg focus:outline-none">
        <a href="{{route('school.show', $school->id)}}">{{$school->name}}</a>
    </li>       
    @endforeach
</ul>
    
<ul>
</ul>
    </x-nice-box>
</x-app-layout>