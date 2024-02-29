<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <h2 class="w-full md:w-1/2 inline-flex font-semibold text-xl text-gray-800">
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    <x-nice-box color="white">
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                <span class="text-gray-600">
                    {{$bill->id}}
                </span>
                <span class="text-blue-400">
                    {{$bill->description}}
                </span>
                <span class="text-gray-400">
                    Created: {{$bill->created_at}}
                </span>
                <span class="text-gray-400">
                    Paid: {{$bill->paid_at}}
                </span>
            </div>
            @if(Auth::user()->getMode() == "Edit")
            <span class="justify-end">
                <form class="inline" action="{{route('bill.edit', $bill->id)}}" method="get">
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>                                  
                    </button>    
                </form>
                <form class="inline" action="{{route('bill.destroy', $bill->id)}}" method="post">
                    @csrf
                    @method('delete')
                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>                                  
                    </button>    
                </form>
            </span>
            @endif
            <form action="{{route('planning.billing')}}" method="post" class="justify-end">
                @csrf
                <input type="hidden" name="year" value="2024">
                <input type="hidden" name="month" value="02">
                <button class="border border-gray-400 bg-white rounded-md px-4 mr-4">
                {{ __('messages.billing') }}
                </button>
            </form>
        </div>
    </x-nice-box>
    <x-nice-box color="white">
        <div class="table w-full">
            @php
            $school = "";
            $course = "";
            $group = "";
            @endphp
            @foreach($planning as $event)
                @if($school != $event->school_name)
                </ul>
                <div class="table-row">
                <h2 class="table-cell text-left text-blue-800 font-black">
                    @php
                    $school = $event->school_name;
                    @endphp
                    {{$school}}
                </h2>
                </div>
                <hr>
                @endif

                @if($course != $event->course_name)
                <br>
                <div class="table-row">
                <h3 class="table-cell text-gray-800 font-black">
                    @php
                    $course = $event->course_name;
                    @endphp
                    {{$course}}
                </h3>
                </div>
                @endif

                @if($group != $event->group_name)
                </ul>
                <br>
                <div class="table-row">
                <h4 class="table-cell text-gray-400 font-black">
                    @php
                    $group = $event->group_name;
                    @endphp
                    {{$group}}
</h4>
                </div>
                <ul>
                @endif

            <li>
                {{date_format(date_create($event->begin),'d/m/Y H:i')}}-{{date_format(date_create($event->end),'H:i')}} 
            </li>
            @endforeach
        </div>
    </x-nice-box>

</x-app-layout>