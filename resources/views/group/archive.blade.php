
        <section  class="section-box">
            <article  class="nice-box">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight py-4">Groupes Archivés</h2>
        <ul>
        @foreach ($inactive as $group)
            <li class="card">
            <x-group-details :group=$group :occurences=$occurences :active=false/>
            </li>
            <ul>
            @foreach($courses as $course)
                @if($group->id == $course.group_id)
                <li>{{ $course->name }}</li>
                @endif
            @endforeach
            </ul>
            
        @endforeach
        </ul>
        </article>
    </section>