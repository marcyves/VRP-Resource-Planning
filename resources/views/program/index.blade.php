<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.programs') }}
        </h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        @if(Auth::user()->getMode() == "Edit")
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($programs as $program)
                        <tr>
                            <td>
                                <a href="{{ route('program.show', $program->id) }}">{{ $program->name }}</a>
                            </td>
                            @if(Auth::user()->getMode() == "Edit")
                                <td class="card-actions">
                                    <form action="{{ route('program.edit', $program->id) }}" method="get">
                                        <x-button-edit />
                                    </form>
                                    <form action="{{ route('program.destroy', $program->id) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <x-button-delete />
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{ route('program.store') }}" method="post" class="group-form nice-form">
            @csrf
            <div class="form-group">
                <x-input-label for="program_name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="program_name" placeholder="{{ __('messages.name') }}" required />
            </div>
            <div class="form-actions">
                <x-button-primary>{{ __('messages.program_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
