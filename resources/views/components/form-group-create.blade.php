@props(['detailsRow' => false])

<div class="form-group">
    <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
    <x-text-input type="text" name="name" id="name" value="{{ old('name') }}" />
    <x-input-error :messages="$errors->get('name')" />
</div>

@if ($detailsRow)
    <div class="form-group-details-row">
        <div class="form-group">
            <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
            <x-text-input type="text" name="short_name" id="short_name" value="{{ old('short_name') }}" />
            <x-input-error :messages="$errors->get('short_name')" />
        </div>
        <div class="form-group">
            <x-input-label for="size">{{ __('messages.size') }}</x-input-label>
            <x-text-input type="text" name="size" id="size" value="{{ old('size', '1') }}" />
            <x-input-error :messages="$errors->get('size')" />
        </div>
        <div class="form-group">
            <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
            <x-text-input type="text" name="year" id="year" value="{{ old('year', now()->format('Y')) }}" />
            <x-input-error :messages="$errors->get('year')" />
        </div>
    </div>
@else
    <div class="form-group">
        <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
        <x-text-input type="text" name="short_name" id="short_name" value="{{ old('short_name') }}" />
        <x-input-error :messages="$errors->get('short_name')" />
    </div>
    <div class="form-group">
        <x-input-label for="size">{{ __('messages.size') }}</x-input-label>
        <x-text-input type="text" name="size" id="size" value="{{ old('size', '1') }}" />
        <x-input-error :messages="$errors->get('size')" />
    </div>
    <div class="form-group">
        <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
        <x-text-input type="text" name="year" id="year" value="{{ old('year', now()->format('Y')) }}" />
        <x-input-error :messages="$errors->get('year')" />
    </div>
@endif
