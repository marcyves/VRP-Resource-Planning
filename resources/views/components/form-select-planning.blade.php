    @if(Auth::user()->getMode() == "Edit")
    <form action="{{ route('planning.create')}}" method="post" class="cool-box">
        @csrf
        <input type="date" name="date" value="{{$year}}-{{substr("0".$month,-2)}}-{{ $day }}" class="my-box">
        <select name="course" class="course-select my-box"  onchange="this.form.submit()">
            <option value disabled selected>-- Select a Course --</option>
            @if($mode == 'selected')
            <option disabled>{{$schools->name}}</option>
                @foreach ($courses as $course)
                    <option selected value="{{$course->id}}"> -({{ $course->program_name }}) {{$course->name}}</option>
                @endforeach
            @elseif($mode == 'single')
            <option disabled>{{$schools->name}}</option>  
                @foreach ($courses as $course)
                    <option value="{{$course->id}}"> -({{ $course->program_name }}) {{$course->name}}</option>
                @endforeach
            @else
            @foreach($schools as $school)
                <option disabled>{{$school->name}}</option>                            
                @foreach ($courses as $course)
                    @if($school->id == $course->school_id)
                    <option value="{{$course->id}}"> -({{ $course->program_name }}) {{$course->name}}</option>
                    @endif
                @endforeach
            @endforeach
            @endif
        </select>
    </form>
    @endif