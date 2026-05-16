<div class="form-group">
    <x-input-label>{{ __('messages.name') }}</x-input-label>
    <x-text-input type="text" name="name" />
</div>
<div class="form-group">
    <x-input-label>{{ __('messages.short_name') }}</x-input-label>
    <x-text-input type="text" name="short_name" />
</div>
<div class="form-group">
    <x-input-label>{{ __('messages.size') }}</x-input-label>
    <x-text-input type="text" name="size" value="1" />
</div>
<div class="form-group">
    <x-input-label>{{ __('messages.year') }}</x-input-label>
    <x-text-input type="text" name="year" value="{{now()->format('Y')}}" />
</div>