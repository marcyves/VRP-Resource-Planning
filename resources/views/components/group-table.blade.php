@props(['groups', 'occurences', 'active' => true])

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
                            <form action="{{ route('group.switch', $group->id) }}" method="get">
                                <button class="icon icon--archive group-archive-toggle" type="submit" title="{{ $active ? __('messages.deactivate') : __('messages.activate') }}" aria-label="{{ $active ? __('messages.deactivate') : __('messages.activate') }}">
                                    @if ($active)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4 7.5h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M6 7.5V19a1.5 1.5 0 0 0 1.5 1.5h9A1.5 1.5 0 0 0 18 19V7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M8 4h8l1.5 3.5h-11L8 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M12 10.5v5.5m0 0 2.25-2.25M12 16l-2.25-2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4 7.5h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M6 7.5V19a1.5 1.5 0 0 0 1.5 1.5h9A1.5 1.5 0 0 0 18 19V7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M8 4h8l1.5 3.5h-11L8 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M12 16V10.5m0 0 2.25 2.25M12 10.5l-2.25 2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('group.edit', $group->id) }}" method="get">
                                <x-button-edit />
                            </form>
                            <form action="{{ route('group.destroy', $group->id) }}" method="post">
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
