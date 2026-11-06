@props(['groups', 'occurences', 'showArchive' => false, 'archived' => false])

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
                            @if ($showArchive)
                                <form action="{{ route('group.switch', $group->id) }}" method="get">
                                    <button class="icon icon--archive group-archive-toggle" type="submit" title="{{ $archived ? __('messages.activate') : __('messages.deactivate') }}" aria-label="{{ $archived ? __('messages.activate') : __('messages.deactivate') }}">
                                        <x-rune-icon :name="$archived ? 'archive-restore' : 'archive'" :size="18" />
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('group.edit', $group->id) }}" method="get">
                                <x-button-edit />
                            </form>
                            <form action="{{ route('group.unlink', $group->id) }}" method="post">
                                @csrf
                                @method('delete')
                                <x-button-delete :label="__('messages.group_unlink_from_course')" />
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
