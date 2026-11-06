@props(['documents'])

@if ($documents->isEmpty())
    <p class="documents-school-empty" role="status"><x-empty-state>{{ __('messages.documents_empty') }}</x-empty-state></p>
@else
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.file') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.year') }}</th>
                    @if (Auth::user()->getMode() == 'Edit')
                        <th>{{ __('messages.actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $document)
                    <tr>
                        <td class="documents-school-table__icon">
                            <img src="{{ asset('/icons/'.substr($document->file_name, -3).'.png') }}" alt="">
                        </td>
                        <td>
                            <a class="documents-school-table__link" target="_blank" rel="noopener noreferrer" href="{{ route('documents.show', $document->id) }}">
                                {{ $document->description }}
                            </a>
                        </td>
                        <td class="date">{{ $document->year }}</td>
                        @if (Auth::user()->getMode() == 'Edit')
                            <td class="card-actions">
                                <form action="{{ route('documents.edit', $document->id) }}" method="get">
                                    <x-button-edit />
                                </form>
                                <button
                                    type="button"
                                    class="icon icon--delete"
                                    aria-label="{{ __('messages.delete') }}"
                                    x-data=""
                                    x-on:click.prevent="$store.documentDelete.request(@js(route('documents.destroy', $document->id)), @js($document->description))"
                                >
                                    <x-rune-icon name="trash" :size="18" />
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
