@props(['school_id','school_name'])
    <form action="{{route('school.show', $school_id)}}" method="get" class="card-content-text">
        @csrf
        <button class="card-title inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
            {{$school_name}}
        </button>    
    </form>
@if(Auth::user()->getMode() == "Edit")
    <div class="card-content-end">
        <form action="{{route('school.edit', $school_id)}}" method="get" class="action">
            <x-button-edit/>
        </form>
        <form action="{{route('school.destroy', $school_id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete/>
        </form>
        <a href="{{route('course.create', $school_id)}}"><x-button-add/></a>
    </div>
@endif
