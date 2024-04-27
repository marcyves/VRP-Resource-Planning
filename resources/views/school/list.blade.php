<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.schools_empty_list') }}
        </h2>
    </x-slot>

    <section  class="nice-box">
    @foreach($schools as $school)
        <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
            <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden mb-4">
                <x-school-header :school_name=$school->name :school_id=$school->id/>
            </div>
        </div>
    @endforeach        
    </section>
</x-app-layout>