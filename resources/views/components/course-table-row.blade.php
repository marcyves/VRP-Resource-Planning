@props(['course'])
<tr>
    <td class="course-table__program">
        <a href="{{ route('program.show', $course->program_id) }}" class="course-table__link">
            <x-program-list-label :name="$course->program_name" :short-description="$course->program_short_description ?? null" />
        </a>
    </td>
    <th scope="row" class="course-table__course">
        <a href="{{ route('course.show', $course->id) }}" class="course-table__link">{{ $course->name }}</a>
    </th>
    <td>{{ $course->semester }}</td>
    <td>{{ $course->sessions }}</td>
    <td>{{ $course->session_length }}</td>
    <td>{{ $course->session_length * $course->sessions }}</td>
    <td>{{ $course->groups_count }}</td>
    <td>{{ $course->groups_count * $course->session_length * $course->sessions }}</td>
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
