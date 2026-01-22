<div class="group-card-content">
    <div class="group-title">
        <a href="{{route('group.show', $group->id)}}">
            {{$group->name}} ({{$group->short_name}})
        </a>
    </div>
    <div class="group-info">
        {{$group->size}} students - {{$group->year}}
    </div>

    @if(Auth::user()->getMode() == "Edit")
    <div class="group-actions">
        <form action="{{route('group.switch', $group->id)}}" method="get">
            <button class="btn-text" type="submit" title="{{ $active ? 'Deactivate' : 'Activate' }}">
                <img src="/icons/arrow-{{ $active ? 'down' : 'up' }}.svg" alt="{{ $active ? 'Down' : 'Up' }}" class="nav-user-icon">
            </button>
        </form>
        <form action="{{route('group.edit', $group->id)}}" method="get">
            <x-button-edit />
        </form>
        <form action="{{route('group.destroy', $group->id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete />
        </form>
    </div>
    @endif
</div>

<ul class="flex-list">
    @foreach($occurences as $occurence)
    @if($group->id == $occurence->group_id)
    <li>
        <strong>{{$occurence->course_name}}</strong>:
        {{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }} -
        {{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}
    </li>
    @endif
    @endforeach
</ul>