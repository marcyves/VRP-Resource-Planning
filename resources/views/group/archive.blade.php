<section class="group-archive-section">
    <div class="group-section-header">
        <h2 class="header-title">{{ __('messages.archived_groups') }}</h2>
    </div>
<div class="group-grid">
        @foreach ($inactive as $group)
        <div class="group-card">
            <x-group-details :group="$group" :occurences="$occurences" :active="false" />

            <div class="group-associated-courses mt-4 pt-4 border-t border-gray-100">
                <h3 class="card-subtitle text-sm">{{ __('messages.associated_courses') }}</h3>
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