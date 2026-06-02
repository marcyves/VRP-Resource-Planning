@props(['groups', 'occurences', 'active' => true])

<ul @class([
    'resource-grid',
    'resource-grid--inactive' => ! $active,
    'resource-grid--group-tiles' => ! $active,
])>
    @foreach ($groups as $group)
        @php
            $groupOccurences = $occurences->where('group_id', $group->id);
        @endphp
        <x-group-card :group="$group" :group-occurences="$groupOccurences" :active="$active" />
    @endforeach
</ul>
