@if (session('school') !== null || session('course') !== null)
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ul>
            @if (session('school') !== null)
                <li>
                    <form action="{{ route('school.show', session('school_id')) }}" method="get">
                        @csrf
                        <button type="submit">{{ session('school') }}</button>
                    </form>
                </li>
            @endif
            @if (session('course') !== null)
                @if (session('school') !== null)
                    <li aria-hidden="true">›</li>
                @endif
                <li>
                    <form action="{{ route('course.show', session('course_id')) }}" method="get">
                        @csrf
                        <button type="submit">{{ session('course') }}</button>
                    </form>
                </li>
            @endif
        </ul>
    </nav>
@endif
