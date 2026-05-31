@if ($breadcrumbUsesSelectors ?? false)
    @php
        $breadcrumbModule = $breadcrumbModule ?? 'workload';
        $breadcrumbShowCourse = $breadcrumbShowCourse ?? false;
        $schoolsUrl = match ($breadcrumbModule) {
            'invoice' => route('invoice.schools'),
            'planning' => route('planning.schools'),
            default => route('home'),
        };
        $selectSchoolUrl = $breadcrumbModule === 'invoice'
            ? route('invoice.selectSchool')
            : route('planning.selectSchool');
        $selectCourseUrl = route('planning.selectCourse');
    @endphp

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ul>
            <li class="breadcrumb__segment">
                <a href="{{ $schoolsUrl }}">{{ __('messages.schools') }}</a>
                @if (Auth::user()->getMode() == 'Edit')
                    <a
                        href="{{ route('home') }}#school-create-panel"
                        class="breadcrumb__add"
                        aria-label="{{ __('messages.school_create') }}"
                        title="{{ __('messages.school_create') }}"
                    >
                        <img src="{{ asset('icons/add-circle-svgrepo-com.svg') }}" alt="" width="18" height="18" decoding="async">
                    </a>
                @endif
            </li>
            <li aria-hidden="true">›</li>
            <li class="breadcrumb__segment">
                <form class="breadcrumb__form" action="{{ $selectSchoolUrl }}" method="post">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <select
                        id="breadcrumb-school"
                        name="school_id"
                        class="breadcrumb__select"
                        aria-label="{{ __('messages.school') }}"
                        onchange="this.form.submit()"
                        required
                    >
                        @if (! session('school_id'))
                            <option value="" disabled selected>{{ __('messages.select_school') }}</option>
                        @endif
                        @foreach ($breadcrumbSchools as $school)
                            <option value="{{ $school->id }}" @selected((int) session('school_id') === (int) $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </li>
            @if ($breadcrumbShowCourse && session('school_id'))
                <li aria-hidden="true">›</li>
                <li class="breadcrumb__segment">
                    <form class="breadcrumb__form" action="{{ $selectCourseUrl }}" method="post">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ url()->current() }}">
                        <select
                            id="breadcrumb-course"
                            name="course_id"
                            class="breadcrumb__select"
                            aria-label="{{ __('messages.course') }}"
                            onchange="this.form.submit()"
                        >
                            <option value="" @selected(! session('course_id'))>{{ __('messages.course') }}</option>
                            @foreach ($breadcrumbCourses as $course)
                                <option value="{{ $course->id }}" @selected((int) session('course_id') === (int) $course->id)>
                                    ({{ $course->program_name }}) {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @if (Auth::user()->getMode() == 'Edit')
                        <a
                            href="{{ route('course.create', session('school_id')) }}"
                            class="breadcrumb__add"
                            aria-label="{{ __('messages.course_create') }}"
                            title="{{ __('messages.course_create') }}"
                        >
                            <img src="{{ asset('icons/add-circle-svgrepo-com.svg') }}" alt="" width="18" height="18" decoding="async">
                        </a>
                    @endif
                </li>
            @endif
        </ul>
    </nav>
@endif
