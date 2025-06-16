@props(['group', 'occurences', 'active'])
<div class="card-content-text">
    <div class="card-title">
            <a href="{{route('group.show', $group->id)}}">{{$group->name}}</a>            
            ({{$group->short_name}})         
            {{$group->size}} students - {{$group->year}}
    </div>
    <ul class="card-subtitle">
        @foreach($occurences as $occurence)
            @if($group->id == $occurence->group_id)
                <li class="ml-2">
                [{{$occurence->course_name}}] - 
                {{date_format(date_create($occurence->begin),'d/m/Y H:i')}}-{{date_format(date_create($occurence->end),'H:i')}}
                </li>
            @endif
        @endforeach
    </ul>
</div>
@if(Auth::user()->getMode() == "Edit") 
<div class="card-content-end">
    <div class="check">
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
            <x-button-edit/>
        </form>
        <form action="{{route('group.destroy', $group->id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete/>    
        </form>
    </div>
</div>
@endif