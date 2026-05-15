@props(['documents'])
@if ($documents->isEmpty())
    <p class="documents-school-empty" role="status">{{ __('messages.documents_empty') }}</p>
@else
    <div class="documents-school-list">
        @foreach ($documents as $document)
            <div class="documents-school-list__row">
                <span class="documents-school-list__icon" aria-hidden="true">
                    <img src="{{ asset('/icons/'.substr($document->file_name, -3).'.png') }}" alt="">
                </span>
                <a class="documents-school-list__link" target="_blank" href="{{ route('documents.show', $document->id) }}">
                    {{ $document->description }}
                </a>
                <span class="documents-school-list__year">{{ $document->year }}</span>
                <div class="documents-school-list__actions">
                    @if (Auth::user()->getMode() == 'Edit')
                        <form action="{{ route('documents.edit', $document->id) }}" method="get">
                            <x-button-edit />
                        </form>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="get">
                            <x-button-delete />
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
