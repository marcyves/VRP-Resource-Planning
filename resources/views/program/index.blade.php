<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.programs') }}
        </h2>
    </x-slot>

    <x-module-tabs :tabs="[
        ['href' => route('dashboard'), 'label' => __('messages.workload_plan'), 'active' => request()->routeIs('dashboard', 'school.dashboard')],
        ['href' => route('school.index'), 'label' => __('messages.schools'), 'active' => request()->routeIs('school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'course.*')],
        ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => request()->routeIs('program.*')],
        ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => request()->routeIs('group.*')],
    ]" />

    <section>
        <ul class="list">
            @foreach ($programs as $program)
            <li class="card">
                <div class="card-content">
                    <a href="{{route('program.show', $program->id)}}" class="card-content-text">
                        <button class="card-title btn-text-link">
                            {{$program->name}}
                        </button>
                    </a>
                    @if(Auth::user()->getMode() == "Edit")
                    <div class="card-content-end">
                        <form action="{{route('program.edit', $program->id)}}" method="get">
                            <x-button-edit />
                        </form>
                        <form action="{{route('program.destroy', $program->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <x-button-delete />
                        </form>
                    </div>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </section>

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('program.store')}}" method="post"
            class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex items-center justify-items-start">
            @csrf
            <x-text-input class="mx-6" type="text" name="name" placeholder="{{ __('messages.name') }}" />
            <x-button-primary>{{ __('messages.program_create') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>