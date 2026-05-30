<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_planning') }}: {{ $date }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    <section>
        <form action="{{ route('planning.store') }}" method="post" class="group-form nice-form">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="course" value="{{ $course->id }}">
            <input type="hidden" name="session_length" value="{{ $session_length }}">

            <div class="form-group">
                <label class="form-label">{{ __('messages.group') }}</label>
                <select name="group" class="form-input">
                    <option value="0" selected>{{ __('messages.new_group_below') }}</option>
                    @foreach ($groups as $group)
                    @if($group->sessions == 0 or $group->sessions == $course->sessions)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.time') }}</label>
                <div class="nav-form">
                    <select name="hour" class="form-input">
                        @for ($h=8;$h<20;$h++)
                            <option value="{{ $h }}">{{ $h }}</option>
                        @endfor
                    </select>
                    <select name="minutes" class="form-input">
                        @for($m=0;$m<60;$m+=5)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <x-form-group-create :course_id="$course->id" />

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('planning.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.plan') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
