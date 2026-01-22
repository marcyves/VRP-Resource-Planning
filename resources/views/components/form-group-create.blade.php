<div class="form-group">
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" />
</div>
<div class="form-group">
    <x-input-label>Short Name</x-input-label>
    <x-text-input type="text" name="short_name" />
</div>
<div class="form-group">
    <x-input-label>Size</x-input-label>
    <x-text-input type="text" name="size" value="1" />
</div>
<div class="form-group">
    <x-input-label>Year</x-input-label>
    <x-text-input type="text" name="year" value="{{now()->format('Y')}}" />
</div>