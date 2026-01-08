<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Programs') }}
        </h2>
    </x-slot>

    <section class="glass-background">
        <ul class="list">
            @foreach ($programs as $program)
            <li class="card glass-background">
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
    <section class="glass-background">
        <form action="{{route('program.store')}}" method="post"
            class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex items-center justify-items-start">
            @csrf
            <x-text-input class="mx-6" type="text" name="name" placeholder="{{ __('messages.name') }}" />
            <x-button-primary>{{ __('messages.program_create') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>