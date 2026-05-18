@props(['course'])
<tr>
    <td class="course-table__actions">
        <form action="{{ route('program.show', $course->program_id) }}" method="get">
            @csrf
            <x-button-primary class="btn-text-link">{{ $course->program_name }}</x-button-primary>
        </form>
    </td>
    <th scope="row">
        <form action="{{ route('course.show', $course->id) }}" method="get">
            @csrf
            <x-button-primary class="btn-text-link">{{ $course->name }}</x-button-primary>
        </form>
    </th>
    <td>{{ $course->semester }}</td>
    <td>{{ $course->sessions }}</td>
    <td>{{ $course->session_length }}</td>
    <td>{{ $course->session_length * $course->sessions }}</td>
    <td>{{ $course->groups_count }}</td>
    <td>{{ $course->groups_count * $course->session_length * $course->sessions }}</td>
    <td>
        @if ($course->recurring)
        {{ __('actions.yes') }}
        @else
        {{ __('actions.no') }}
        @endif
    </td>
    <td>@money($course->rate)</td>
    <td>@money($course->rate * $course->session_length * $course->sessions * $course->groups_count)</td>
    @if (Auth::user()->getMode() == 'Edit')
    <td>
            <form action="{{ route('course.edit', $course->id) }}" method="get" class="course-table__actions-form">
                <x-button-edit />
            </form>
            <form action="{{ route('course.destroy', $course->id) }}" method="post" class="course-table__actions-form">
                @csrf
                @method('delete')
                <x-button-delete />
            </form>
    </td>
    @endif
</tr>
