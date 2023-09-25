<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{$school_name}} Details
        </h2>
    </x-slot>

    <x-advanced-course-table :courses=$courses :school_name=$school_name :school_id=$school_id/>

</x-app-layout>