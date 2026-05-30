@props(['groups'])

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.short_name') }}</th>
                <th>{{ __('messages.size') }}</th>
                <th>{{ __('messages.year') }}</th>
                @if (Auth::user()->getMode() == 'Edit')
                    <th>{{ __('messages.actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ $group->short_name }}</td>
                    <td class="date">{{ $group->size }} {{ __('messages.students') }}</td>
                    <td class="date">{{ $group->year }}</td>
                    @if (Auth::user()->getMode() == 'Edit')
                        <td class="card-actions">
                            <form action="{{ route('group.link', $group->id) }}" method="get">
                                <x-button-secondary type="submit" title="{{ __('messages.add') }}">
                                    <img src="/icons/arrow-up.svg" alt="{{ __('messages.add') }}">
                                </x-button-secondary>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ Auth::user()->getMode() == 'Edit' ? 5 : 4 }}" class="group-table__empty">{{ __('messages.no_available_group') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
