@props(['groups', 'occurences'])

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.short_name') }}</th>
                <th>{{ __('messages.size') }}</th>
                <th>{{ __('messages.year') }}</th>
                <th>{{ __('messages.sessions') }}</th>
                @if (Auth::user()->getMode() == 'Edit')
                    <th>{{ __('messages.actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $group)
                @php
                    $groupOccurences = $occurences->where('group_id', $group->id);
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('group.show', $group->id) }}">{{ $group->name }}</a>
                    </td>
                    <td>{{ $group->short_name }}</td>
                    <td class="date">{{ $group->size }} {{ __('messages.students') }}</td>
                    <td class="date">{{ $group->year }}</td>
                    <td>
                        <x-group-table-sessions :group-occurences="$groupOccurences" />
                    </td>
                    @if (Auth::user()->getMode() == 'Edit')
                        <td class="card-actions">
                            <form action="{{ route('group.edit', $group->id) }}" method="get">
                                <x-button-edit />
                            </form>
                            <form action="{{ route('group.unlink', $group->id) }}" method="post">
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
