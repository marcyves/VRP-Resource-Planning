<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.company_edit') }}</h2>
    </x-slot>

    <x-settings-module-tabs active="company" />

    <section class="company-edit">
        <form action="{{ route('company.update') }}" method="post" class="school-create-form company-edit-form nice-form nice-form--wide">
            @csrf
            @method('PUT')

            <div class="company-form-block">
                <h3 class="company-form-block__title">{{ __('messages.terminology_profile') }}</h3>
                <p class="form-hint">{{ __('messages.terminology_profile_hint') }}</p>
                <div class="school-form-input">
                    <x-input-label for="terminology_profile" :value="__('messages.terminology_profile')" />
                    <x-terminology-profile-select
                        :selected="old('terminology_profile', $company->terminology_profile ?? 'education')"
                    />
                    <x-input-error :messages="$errors->get('terminology_profile')" />
                </div>
            </div>

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
                <h3 class="company-form-block__title">{{ __('messages.billing_bank_account') }}</h3>
                <p class="form-hint">{{ __('messages.billing_bank_account_help') }}</p>

                @if ($bankAccounts->isEmpty())
                    <p class="treasury-empty">{{ __('messages.billing_bank_account_empty') }}</p>
                    <p>
                        <a class="btn btn-secondary btn--compact" href="{{ route('treasury.bank.index') }}">{{ __('messages.manage_bank_accounts') }}</a>
                    </p>
                @else
                    <div class="school-form-input">
                        <x-input-label for="billing_bank_account_id" :value="__('messages.bank_select_account')" />
                        <select name="billing_bank_account_id" id="billing_bank_account_id" class="form-input">
                            <option value="">{{ __('messages.billing_bank_account_none_option') }}</option>
                            @foreach ($bankAccounts->groupBy(fn ($a) => $a->bank->name) as $bankName => $accounts)
                                <optgroup label="{{ $bankName }}">
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('billing_bank_account_id', $company->billing_bank_account_id) == $account->id)>
                                            {{ $account->displayName() }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <p>
                        <a class="btn btn-secondary btn--compact" href="{{ route('treasury.bank.index') }}">{{ __('messages.manage_bank_accounts') }}</a>
                    </p>
                    @if ($company->billingBankAccount)
                        <div class="company-billing-preview">
                            <x-company-billing-details :account="$company->billingBankAccount" />
                        </div>
                    @endif
                @endif
            </div>

            <div class="form-actions company-form-actions">
                <a href="{{ route('company.show') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
