<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.schools_list') }}
        </h2>
        @if(Auth::user()->getMode() == "Edit")
        <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0  md:items-center justify-end md:space-x-3">
            <a class="p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('school.list')}}"> {{ __('messages.school_no_course') }}</a>
        </div>
        @endif
    </x-slot>

    <section  class="nice-page">
        <ul>
            @foreach ($schools as $school)
            <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
            flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
                @php
                    $school_name=$school->name;
                    $school_id=$school->id;
                @endphp
                <x-school-header :school_name=$school_name :school_id=$school_id/>
            </li>
            @endforeach
        </ul>  
    </section>

    @if(Auth::user()->getMode() == "Edit")
    <section class="nice-page">
        <form action="{{route('school.store')}}" method="post" 
        class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex items-center justify-items-start">
            @csrf
            <x-text-input class="mx-6" type="text" name="name" id="name" placeholder="{{ __('messages.name') }}"/>
            <x-primary-button>{{ __('messages.school_create') }}</x-primary-button>
        </form>
    </section>
    @endif

</x-app-layout>