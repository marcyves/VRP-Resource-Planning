@props(['group', 'occurences', 'active'])
<div class="card-content">
    <div class="card-title">
        <a href="{{route('group.show', $group->id)}}" class="card-title">
            {{$group->name}}
            ({{$group->short_name}})
            {{$group->size}} students - {{$group->year}}
        </a>
    </div>
    @if(Auth::user()->getMode() == "Edit")
    <div class="card-content-end">
        <div class="check flex flex-row gap-2">
            <form action="{{route('group.switch', $group->id)}}" method="get">
                <button class="icon green" type="submit">
                    @if($active)
                    <img src="/icons/arrow-down.svg" alt="Down">
                    @else
                    <img src="/icons/arrow-up.svg" alt="Up">
                    @endif
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
    </div>
    @endif
</div>
<ul class="card-line-two">
    @foreach($occurences as $occurence)
    @if($group->id == $occurence->group_id)
    <li>
        [{{$occurence->course_name}}] -
        {{date_format(date_create($occurence->begin),'d/m/Y H:i')}}-{{date_format(date_create($occurence->end),'H:i')}}
    </li>
    @endif
    @endforeach
</ul>