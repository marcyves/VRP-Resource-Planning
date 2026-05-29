<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.school_edit') }}</h2>
    </x-slot>

    <section>
        <form action="{{route('school.update', $school->id)}}" method="post" class="school-create-form">
            @csrf
            <div class="school-form-input">
                <x-text-input type="text" name="name" placeholder="{{ __('messages.name') }}" value="{{old('name',$school->name)}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="code" placeholder="{{ __('messages.code') }}" value="{{old('code',$school->code)}}" />
            </div>
            <fieldset class="school-form-fieldset">
                <legend>{{ __('messages.legal_identifiers') }} ({{ __('messages.b2b_optional') }})</legend>
                <div class="school-form-input">
                    <x-text-input type="text" name="siren" placeholder="{{ __('messages.siren') }}" maxlength="9" pattern="[0-9]{9}" value="{{ old('siren', $school->siren) }}" />
                </div>
                <div class="school-form-input">
                    <x-text-input type="text" name="siret" placeholder="{{ __('messages.siret') }}" maxlength="14" pattern="[0-9]{14}" value="{{ old('siret', $school->siret) }}" />
                </div>
                <div class="school-form-input">
                    <x-text-input type="text" name="vat_number" placeholder="{{ __('messages.vat_number') }}" value="{{ old('vat_number', $school->vat_number) }}" />
                </div>
            </fieldset>
            <div class="school-form-input">
                <x-text-input type="text" name="address2" id="address2" placeholder="{{ __('messages.address') }} 1" value="{{old('address2',$school->address2)}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="address" id="address" placeholder="{{ __('messages.address') }} 2" value="{{old('address',$school->address)}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city',$school->city)}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="zip" id="zip" placeholder="{{ __('messages.zip')}}" value="{{old('zip',$school->zip)}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country',$school->country)}}" />
            </div>
            <x-button-primary>{{ __('messages.update') }}</x-button-primary>
        </form>
    </section>
</x-app-layout>