<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.company_edit') }}</h2>
    </x-slot>

    <section class="company-edit">
        <form action="{{ route('company.update') }}" method="post" class="school-create-form company-edit-form">
            @csrf
            @method('PUT')

            <div class="company-form-block">
                <h3 class="company-form-block__title">{{ __('messages.legal_identifiers') }}</h3>
                <p class="form-hint">{{ __('messages.company_legal_edit_hint') }}</p>

                <div class="school-form-input">
                    <x-input-label for="siren" :value="__('messages.siren')" />
                    <x-text-input type="text" name="siren" id="siren" maxlength="9" pattern="[0-9]{9}" value="{{ old('siren', $company->siren) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="siret" :value="__('messages.siret')" />
                    <x-text-input type="text" name="siret" id="siret" maxlength="14" pattern="[0-9]{14}" value="{{ old('siret', $company->siret) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="vat_number" :value="__('messages.vat_number')" />
                    <x-text-input type="text" name="vat_number" id="vat_number" value="{{ old('vat_number', $company->vat_number) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="legal_form" :value="__('messages.legal_form')" />
                    <x-text-input type="text" name="legal_form" id="legal_form" value="{{ old('legal_form', $company->legal_form) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="share_capital" :value="__('messages.share_capital')" />
                    <x-text-input type="text" name="share_capital" id="share_capital" placeholder="{{ __('messages.share_capital_example') }}" value="{{ old('share_capital', $company->share_capital) }}" />
                </div>
            </div>

            <div class="company-form-block" x-data="{
                users: {{ Js::from($companyUsers->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'website' => $u->website,
                ])) }},
                selectedId: @js((string) old('contact_user_id', $company->contact_user_id ?? '')),
                get contact() {
                    return this.users.find(u => String(u.id) === String(this.selectedId)) ?? null;
                }
            }">
                <h3 class="company-form-block__title">{{ __('messages.contact') }}</h3>
                <p class="form-hint">{{ __('messages.company_contact_hint') }}</p>

                <div class="school-form-input">
                    <x-input-label for="contact_user_id" :value="__('messages.company_contact_user')" />
                    <select name="contact_user_id" id="contact_user_id" class="form-input" x-model="selectedId">
                        <option value="">{{ __('messages.company_contact_select') }}</option>
                        @foreach ($companyUsers as $user)
                        <option value="{{ $user->id }}" @selected((string) old('contact_user_id', $company->contact_user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="school-form-input">
                    <x-input-label for="contact_email_preview" :value="__('messages.email')" />
                    <x-text-input type="email" id="contact_email_preview" readonly class="form-input--readonly" x-bind:value="contact?.email ?? ''" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="contact_phone_preview" :value="__('messages.phone')" />
                    <x-text-input type="text" id="contact_phone_preview" readonly class="form-input--readonly" x-bind:value="contact?.phone ?? ''" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="contact_website_preview" :value="__('messages.website')" />
                    <x-text-input type="text" id="contact_website_preview" readonly class="form-input--readonly" x-bind:value="contact?.website ?? ''" />
                </div>
            </div>

            <div class="company-form-block">
                <h3 class="company-form-block__title">{{ __('messages.iban') }}</h3>

                <div class="school-form-input">
                    <x-input-label for="bank_name" :value="__('messages.bank')" />
                    <x-text-input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $company->bank_name) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="iban_name" :value="__('messages.account_holder')" />
                    <x-text-input type="text" name="iban_name" id="iban_name" value="{{ old('iban_name', $company->iban_name) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="bank" :value="__('messages.bank_code')" />
                    <x-text-input type="text" name="bank" id="bank" value="{{ old('bank', $company->bank) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="branch" :value="__('messages.branch_code')" />
                    <x-text-input type="text" name="branch" id="branch" value="{{ old('branch', $company->branch) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="account" :value="__('messages.account_number')" />
                    <x-text-input type="text" name="account" id="account" value="{{ old('account', $company->account) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="key" :value="__('messages.key')" />
                    <x-text-input type="text" name="key" id="key" value="{{ old('key', $company->key) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="iban" :value="__('messages.iban_code')" />
                    <x-text-input type="text" name="iban" id="iban" value="{{ old('iban', $company->iban) }}" />
                </div>
                <div class="school-form-input">
                    <x-input-label for="bic" :value="__('messages.bic_code')" />
                    <x-text-input type="text" name="bic" id="bic" value="{{ old('bic', $company->bic) }}" />
                </div>
            </div>

            <div class="form-actions company-form-actions">
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
                <a href="{{ route('company.show') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </section>
</x-app-layout>
