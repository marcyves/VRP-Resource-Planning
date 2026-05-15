<nav class="breadcrumb" aria-label="Breadcrumb">
    <ul>
        <li>
            <a href="{{ route('company.show') }}">
                {{ Auth::user()->company->name }}
            </a>
        </li>
        @if (session('school') !== null)
        <li aria-hidden="true">›</li>
        <li>
            <form action="{{ route('school.show', session('school_id')) }}" method="get">
                @csrf
                <button type="submit">{{ session('school') }}</button>
            </form>
        </li>
        @endif
        @if (session('course') !== null)
        <li aria-hidden="true">›</li>
        <li>
            <form action="{{ route('course.show', session('course_id')) }}" method="get">
                @csrf
                <button type="submit">{{ session('course') }}</button>
            </form>
        </li>
        @endif
    </ul>
</nav>
