@props(['school_id','school_name'])
<div class="card-content">
    <a href="{{route('school.show', $school_id)}}" class="card-content-text">
        <x-button-primary
            class="card-title btn-text-link">
            {{html_entity_decode($school_name)}}
        </x-button-primary>
    </a>
    @if(Auth::user()->getMode() == "Edit")
    <div class="card-content-end">
        <form action="{{route('school.edit', $school_id)}}" method="get">
            <x-button-edit />
        </form>
        <form action="{{route('school.destroy', $school_id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete />
        </form>
        <a href="{{route('course.create', $school_id)}}"><x-button-add /></a>
        <form action="{{route('invoice.create')}}" method="get">
            @csrf
            <input type="hidden" name="school_id" value="{{$school_id}}">
            <input type="submit" value="$"
                class="btn-input">
        </form>
    </div>
    @endif
</div>