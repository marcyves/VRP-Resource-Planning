@props(['groupOccurences'])

@if ($groupOccurences->isEmpty())
    <span class="group-table__empty">—</span>
@else
    <details class="group-table-sessions">
        <summary>{{ $groupOccurences->count() }}</summary>
        <ul class="group-table-sessions__list">
            @foreach ($groupOccurences as $occurence)
                <li>
                    <a class="group-session-link" href="{{ route('planning.edit', $occurence->planning_id) }}">
                        <strong class="group-session-course">{{ $occurence->course_name }}</strong>
                        <span class="group-session-begin">{{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }}</span>
                        <span class="group-session-end">{{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </details>
@endif
