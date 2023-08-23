
<form action="{{ route('planning.insert', $day)}}" method="post" class="p-2 bg-blue-100">
    @csrf
    <input type="hidden" name="day" value={{$day}}>
    <input type="hidden" name="month" value={{$month}}>
    <input type="hidden" name="year" value={{$year}}>
    <select name="course" class="rounded-md mt-4 py-0 pl-2 pr-8 overflow-clip w-28 mb-2">
        @foreach ($courses as $course)
        <option value="{{$course->id}}">{{$course->name}}</option>                            
        @endforeach
    </select>
    <br>
    <input 
    class="inline-flex items-center p-2 my-2 text-sm border border-gray-400 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
    type="submit" value="Set">
</form>
