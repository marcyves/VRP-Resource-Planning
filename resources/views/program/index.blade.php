<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Programs') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <ul>
            @foreach ($programs as $program)
            <li>
                {{$program->name}}
            </li>
            @endforeach
        </ul>
    </x-nice-box>

</x-app-layout>