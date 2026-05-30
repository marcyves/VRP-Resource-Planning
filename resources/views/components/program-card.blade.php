@props(['program'])
<li>
    <div class="card-content">
        <a href="{{ route('program.show', $program->id) }}" class="card-content-text">
            <x-button-primary class="card-title btn-text-link">
                {{ $program->name }}
            </x-button-primary>
        </a>
        @if (Auth::user()->getMode() == 'Edit')
            <div class="card-content-end">
                <form action="{{ route('program.edit', $program->id) }}" method="get">
                    <x-button-edit />
                </form>
                <button
                    type="button"
                    class="icon icon--delete"
                    aria-label="{{ __('messages.delete') }}"
                    x-data=""
                    x-on:click.prevent="$store.programDelete.request(@js(route('program.destroy', $program->id)), @js($program->name))"
                >
                    <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
                </button>
            </div>
        @endif
    </div>
</li>
