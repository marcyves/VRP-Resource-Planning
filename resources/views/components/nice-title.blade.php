@props(['title', 'color'])
<div class="flex flew-row place-content-between bg-{{$color}} p-2 rounded-md">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{$title}}</h2>
    <div>
        {{ $slot}}
    </div>
</div>