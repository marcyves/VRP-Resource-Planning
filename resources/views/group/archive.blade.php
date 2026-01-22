<section class="group-archive-section">
    <div class="group-section-header">
        <h2 class="header-title">{{ __('Groupes Archivés') }}</h2>
    </div>

    @push('styles')
    @vite(['resources/css/groups.css'])
    @endpush

    <div class="group-grid">
        @foreach ($inactive as $group)
        <div class="group-card glass-background">
            <x-group-details :group="$group" :occurences="$occurences" :active="false" />

            <div class="group-associated-courses mt-4 pt-4 border-t border-gray-100">
                <h3 class="card-subtitle text-sm">{{ __('Cours associés') }}</h3>
                <ul class="flex-list">
                    @foreach($courses as $course)
                    @if($group->id == $course->group_id)
                    <li>{{ $course->name }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
        @endforeach
    </div>
</section>