<x-app-layout>
    @push('styles')
    @vite(['resources/css/plannings.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('Group Planification: ') . $date }}</h2>
    </x-slot>

    <section class="glass-background">
        <form action="{{route('planning.store')}}" method="post" class="group-form glass-background-solid">
            @csrf
            <input type="hidden" name="date" value="{{$date}}">
            <input type="hidden" name="course" value="{{$course->id}}">
            <input type="hidden" name="session_length" value="{{$session_length}}">

            <div class="form-group">
                <label class="form-label">Group</label>
                <select name="group" class="form-input">
                    <option value="0" selected>New group below</option>
                    @foreach ($groups as $group)
                    @if($group->sessions == 0 or $group->sessions == $course->sessions)
                    <option value="{{$group->id}}">{{$group->name}}</option>
                    @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Time</label>
                <div class="nav-form">
                    <select name="hour" class="form-input">
                        @for ($h=8;$h<20;$h++)
                            <option value="{{$h}}">{{$h}}</option>
                            @endfor
                    </select>
                    <select name="minutes" class="form-input">
                        @for($m=0;$m<60;$m+=5)
                            <option value="{{$m}}">{{$m}}</option>
                            @endfor
                    </select>
                </div>
            </div>

            <x-form-group-create :course_id="$course->id" />

            <x-button-primary>Plan</x-button-primary>
        </form>
    </section>
</x-app-layout>