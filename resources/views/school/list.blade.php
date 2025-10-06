<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.schools_empty_list') }}
        </h2>
    </x-slot>

    <section >
        <ul>
            @foreach ($schools as $school)
            <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
            flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
                <x-school-header :school_name="$school->name" :school_id="$school->id"/>
            </li>
            @endforeach
        </ul>  
    </section>

</x-app-layout>