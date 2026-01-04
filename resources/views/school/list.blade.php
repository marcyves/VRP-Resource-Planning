<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.schools_empty_list') }}
        </h2>
    </x-slot>

    <section>
        <ul>
            @foreach ($schools as $school)
            <li class="resource-list-item">
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
            </li>
            @endforeach
        </ul>
    </section>

</x-app-layout>