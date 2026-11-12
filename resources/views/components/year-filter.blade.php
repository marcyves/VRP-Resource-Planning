@php
    $yearChoices = $years ?? null;
    if ($yearChoices === null && auth()->check() && ! auth()->user()->isSuperAdmin()) {
        $yearChoices = auth()->user()->getSchools()->getYears();
    }
    $selectedYear = session('current_year', now()->format('Y'));
@endphp

<form class="nav-form" action="{{ route('date.select') }}" method="post">
    @csrf
    <select name="current_year" aria-label="{{ __('messages.year') }}" onchange="this.form.submit()">
        <option value="all" @selected($selectedYear === 'all')>{{ __('actions.select_all') }}</option>
        @foreach ($yearChoices ?? [] as $year)
            <option value="{{ $year->year }}" @selected((string) $selectedYear === (string) $year->year)>{{ $year->year }}</option>
        @endforeach
    </select>
</form>
