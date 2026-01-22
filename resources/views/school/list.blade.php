<x-app-layout>
    @push('styles')
    @vite(['resources/css/schools.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.schools_empty_list') }}</h2>
    </x-slot>

    <section class="schools-list-container">
        <ul class="resource-grid">
            @foreach ($schools as $school)
            <li class="resource-card glass-background">
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
            </li>
            @endforeach
        </ul>
    </section>
</x-app-layout>