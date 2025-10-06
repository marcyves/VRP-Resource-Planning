<x-app-layout>
    <x-slot name="header">
            <h2>
                {{ __('Programs') }}
            </h2>
    </x-slot>

    <section>
        <ul class="list">
            @foreach ($programs as $program)
            <li class="card">
            <div class="card-content-text">
                <form action="{{route('program.show', $program->id)}}" method="get">
                    @csrf
                    <button class="card-title inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        {{$program->name}}
                    </button>    
                </form>
                </div>
                @if(Auth::user()->getMode() == "Edit")
                    <div class="card-content-end">
                    <div class="check">
                    <form class="inline" action="{{route('program.edit', $program->id)}}" method="get">
                        <x-button-edit/>
                    </form>
                    <form class="inline" action="{{route('program.destroy', $program->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <x-button-delete/>
                    </form>
                    </div>
                    </div>
                @endif
            </li>
            @endforeach
        </ul>
    </section>

    @if(Auth::user()->getMode() == "Edit")
        <section >
            <form action="{{route('program.store')}}" method="post"
            class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex items-center justify-items-start">
                @csrf
                <x-text-input class="mx-6" type="text" name="name"  placeholder="{{ __('messages.name') }}"/>
                <x-primary-button>{{ __('messages.program_create') }}</x-primary-button>
            </form>
        </section>
    @endif
</x-app-layout>