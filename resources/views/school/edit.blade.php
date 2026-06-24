<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.school_edit') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <form action="{{ route('school.update', $school->id) }}" method="post" class="school-create-form nice-form">
            @csrf
            @method('put')

            <div class="school-form-input">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{ old('name', $school->name) }}" required />
            </div>
            <div class="school-form-input">
                <x-input-label for="code">{{ __('messages.code') }}</x-input-label>
                <x-text-input type="text" name="code" id="code" value="{{ old('code', $school->code) }}" />
            </div>

            <fieldset class="school-form-fieldset">
                <legend>{{ __('messages.legal_identifiers') }} ({{ __('messages.b2b_optional') }})</legend>
                <div class="school-form-input">
                    <x-input-label for="siren">{{ __('messages.siren') }}</x-input-label>
                    <x-text-input type="text" name="siren" id="siren" maxlength="9" pattern="[0-9]{9}" value="{{ old('siren', $school->siren) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="siret">{{ __('messages.siret') }}</x-input-label>
                    <x-text-input type="text" name="siret" id="siret" maxlength="14" pattern="[0-9]{14}" value="{{ old('siret', $school->siret) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="vat_number">{{ __('messages.vat_number') }}</x-input-label>
                    <x-text-input type="text" name="vat_number" id="vat_number" value="{{ old('vat_number', $school->vat_number) }}" />
                </div>
                <div class="form-group">
                    <x-input-label for="electronic_address">Adresse électronique (PEPPOL 0225)</x-input-label>
                    <x-text-input type="text" name="electronic_address" id="electronic_address" placeholder="315143296_12712" value="{{ old('electronic_address', $school->electronic_address) }}" />
                </div>
            </fieldset>

            <div class="school-form-input">
                <x-input-label for="address2">{{ __('messages.address') }} 1</x-input-label>
                <x-text-input type="text" name="address2" id="address2" value="{{ old('address2', $school->address2) }}" />
            </div>
            <div class="school-form-input">
                <x-input-label for="address">{{ __('messages.address') }} 2</x-input-label>
                <x-text-input type="text" name="address" id="address" value="{{ old('address', $school->address) }}" />
            </div>
            <div class="school-form-input">
                <x-input-label for="city">{{ __('messages.city') }}</x-input-label>
                <x-text-input type="text" name="city" id="city" value="{{ old('city', $school->city) }}" />
            </div>
            <div class="school-form-input">
                <x-input-label for="zip">{{ __('messages.zip') }}</x-input-label>
                <x-text-input type="text" name="zip" id="zip" value="{{ old('zip', $school->zip) }}" />
            </div>
            <div class="school-form-input">
                <x-input-label for="country">{{ __('messages.country') }}</x-input-label>
                <x-text-input type="text" name="country" id="country" value="{{ old('country', $school->country) }}" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('school.show', $school->id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
