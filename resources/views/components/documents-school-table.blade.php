@props(['documents'])
<section>
    <table>
        <thead>
            <tr>
                <th scope="col">Type</th>
                <th scope="col">Description</th>
                <th scope="col">Year</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
            <tr>
                <td>
                    <img src="{{ asset('/icons/'.substr($document->file_name, -3).'.png') }}" alt="{{ substr($document->file_name, -3) }}">
                </td>
                <th scope="row">
                    <a target="_blank"
                        href="{{ route('documents.show', $document->id) }}">
                        {{ $document->description }}
                    </a>
                </th>
                <td>{{ $document->year }}</td>
                <td>
                    @if (Auth::user()->getMode() == 'Edit')
                    <form action="{{ route('documents.edit', $document->id) }}" method="get">
                        <x-button-edit />
                    </form>
                    <form action="{{ route('documents.destroy', $document->id) }}" method="get">
                        <x-button-delete />
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>
