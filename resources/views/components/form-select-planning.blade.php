    @if(Auth::user()->getMode() == "Edit")
    <form action="{{ route('planning.create')}}" method="post" class="cool-box">
        @csrf
        <input type="date" name="date" value="{{$year}}-{{substr("0".$month,-2)}}-{{ $day }}" class="glass-background">
        <label>Select a Course: </label>
        <select name="course" class="course-select my-box" onchange="this.form.submit()">
            @if($mode == 'selected')
            <optgroup label="{{$schools->name}}">
                @foreach ($courses as $course)
                <option selected value="{{$course->id}}">({{ $course->program_name }}) {{$course->name}}</option>
                @endforeach
                @elseif($mode == 'single')
            <optgroup label="{{$schools->name}}">
                @foreach ($courses as $course)
                <option value="{{$course->id}}">({{ $course->program_name }}) {{$course->name}}</option>
                @endforeach
                @else
                @foreach($schools as $school)
            <optgroup label="{{$school->name}} ">
                @foreach ($courses as $course)
                @if($school->id == $course->school_id)
                <option value="{{$course->id}}">({{ $course->program_name }}) {{$course->name}}</option>
                @endif
                @endforeach
                @endforeach
                @endif
        </select>
        <input type="submit" value="Ok">
    </form>
    @endif