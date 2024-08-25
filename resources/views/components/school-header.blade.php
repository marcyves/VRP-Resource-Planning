@props(['school_id','school_name'])
<div class="card">
    <div class="card-content-text">
        <form action="{{route('school.show', $school_id)}}" method="get">
            @csrf
            <button class="card-title inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                {{$school_name}}
            </button>    
        </form>
    </div>
    @if(Auth::user()->getMode() == "Edit")
    <div class="card-content-end">
        <div class="check">
            <form action="{{route('school.edit', $school_id)}}" method="get" class="action">
                <x-button-edit/>
            </form>
            <form action="{{route('school.destroy', $school_id)}}" method="post">
                @csrf
                @method('delete')
                <x-button-delete/>
            </form>
            <a
            class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('course.create', $school_id)}}">{{__('messages.add_course')}}</a>
        </div>
    </div>
    @endif
</div>