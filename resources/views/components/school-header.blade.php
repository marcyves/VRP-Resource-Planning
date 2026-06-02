@props(['school_id', 'school_name'])
<div class="school-header card-content card-content--school">
    <a href="{{ route('school.show', $school_id) }}" class="school-header__name">
        {{ html_entity_decode($school_name) }}
    </a>
    @if (Auth::user()->getMode() == 'Edit')
        <div class="school-header__tools" role="toolbar" aria-label="{{ __('messages.actions') }}">
            <form action="{{ route('school.edit', $school_id) }}" method="get">
                <x-button-edit />
            </form>
            <form action="{{ route('school.destroy', $school_id) }}" method="post">
                @csrf
                @method('delete')
                <x-button-delete />
            </form>
            <a href="{{ route('course.create', $school_id) }}"><x-button-add /></a>
            <x-button-invoice-manual :school_id="$school_id" />
        </div>
    @endif
</div>
