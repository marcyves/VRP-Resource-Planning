@props(['school_id','school_name'])
<div class="school-header">
    <form action="{{route('school.show', $school_id)}}" method="get" class="school-header-name">
        @csrf
        <button class="card-title inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
            {{$school_name}}
        </button>    
    </form>
@if(Auth::user()->getMode() == "Edit")
    <div class="school-header-actions">
        <form action="{{route('school.edit', $school_id)}}" method="get" class="action">
            <x-button-edit/>
        </form>
        <form action="{{route('school.destroy', $school_id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete/>
        </form>
        <a
        class="cool-box"
        href="{{route('course.create', $school_id)}}">{{__('messages.add_course')}}</a>
    </div>
@endif
</div>
