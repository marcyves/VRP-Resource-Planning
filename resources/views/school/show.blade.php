<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ html_entity_decode($school->name) }}
            @if ($school->isMentoring())
                <span class="status-chip">{{ __('messages.school_context_mentoring') }}</span>
            @endif
        </h2>
    </x-slot>

    @php
        $school_name = $school->name;
        $school_id = $school->id;
    @endphp

    <x-school-detail-tabs :school="$school" :panel="$panel" />

    <div class="school-details-grid">
        @if ($panel === 'courses')
            <section>
                <article>
                    <x-school-header :school_name="$school_name" :school_id="$school_id" :show-name="false" />
                    <x-course-table :courses="$courses" :school_name="$school_name" :school_id="$school_id" />
                </article>
            </section>

            <section>
                <h3 class="school-section-header">{{ __('messages.invoices') }}</h3>
                <div class="bills">
                    <x-table-invoices :invoices="$invoices" />
                </div>
            </section>

            <x-school-billing-section
                :school="$school"
                :billing-data="$billingData"
                :monthly-hours="$monthlyHours"
                :monthly-gain="$monthlyGain"
                :current-year="$billingYear"
                :current-month="$currentMonth"
                :months="$months"
                :years="$years"
                :bills="$bills"
                :by-date="$billingByDate"
                :has-previous-unbilled="$hasPreviousUnbilled"
            />
        @elseif ($panel === 'groups')
            <section>
                <header class="school-section-header-row">
                    <h3 class="school-section-header">@schoolMsg('groups')</h3>
                    <p class="form-hint">@schoolMsg('school_groups_help')</p>
                </header>

                @if ($groups->isEmpty() && $inactiveGroups->isEmpty())
                    <p class="program-empty" role="status">@schoolMsg('no_group')</p>
                @else
                    @if ($groups->isNotEmpty())
                        <x-group-table :groups="$groups" :occurences="$occurences" :active="true" />
                    @endif

                    @if ($inactiveGroups->isNotEmpty())
                        <h4 class="school-section-header school-section-header--sub">@schoolMsg('inactive_groups')</h4>
                        <x-group-table :groups="$inactiveGroups" :occurences="$occurences" :active="false" />
                    @endif
                @endif
            </section>
        @elseif ($panel === 'details')
            <section>
                <header class="school-section-header-row school-details-panel__header">
                    <h3 class="school-section-header">{{ __('messages.details') }}</h3>
                    @if (Auth::user()->getMode() == 'Edit')
                        <a class="btn btn-secondary" href="{{ route('school.edit', $school->id) }}">{{ __('messages.edit') }}</a>
                    @endif
                </header>

                <dl class="school-details-list">
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.name') }}</dt>
                        <dd>{{ html_entity_decode($school->name) }}</dd>
                    </div>
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.code') }}</dt>
                        <dd>{{ $school->code ?: '—' }}</dd>
                    </div>
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.school_context') }}</dt>
                        <dd>{{ $school->contextLabel() }}</dd>
                    </div>
                    <div class="school-details-list__item school-details-list__item--full">
                        <dt>{{ __('messages.address') }}</dt>
                        <dd>
                            @if ($school->address2 || $school->address || $school->city || $school->zip || $school->country)
                                @if ($school->address2){{ $school->address2 }}<br>@endif
                                @if ($school->address){{ $school->address }}<br>@endif
                                {{ trim(($school->zip ?? '').' '.($school->city ?? '')) }}@if ($school->country)<br>{{ $school->country }}@endif
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($school->phone)
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.phone') }}</dt>
                        <dd>{{ $school->phone }}</dd>
                    </div>
                    @endif
                    @if ($school->email)
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.email') }}</dt>
                        <dd>{{ $school->email }}</dd>
                    </div>
                    @endif
                    @if ($school->website)
                    <div class="school-details-list__item">
                        <dt>{{ __('messages.website') }}</dt>
                        <dd><a href="{{ $school->website }}" target="_blank" rel="noopener noreferrer">{{ $school->website }}</a></dd>
                    </div>
                    @endif
                    <div class="school-details-list__item school-details-list__item--full">
                        <dt>{{ __('messages.legal_identifiers') }}</dt>
                        <dd>
                            <ul class="school-details-list__plain">
                                <li>{{ __('messages.siren') }} : {{ $school->siren ?: '—' }}</li>
                                <li>{{ __('messages.siret') }} : {{ $school->siret ?: '—' }}</li>
                                <li>{{ __('messages.vat_number') }} : {{ $school->vat_number ?: '—' }}</li>
                                <li>{{ __('messages.electronic_address') }} : {{ $school->electronic_address ?: '—' }}</li>
                            </ul>
                        </dd>
                    </div>
                    @if ($school->description)
                    <div class="school-details-list__item school-details-list__item--full">
                        <dt>{{ __('messages.description') }}</dt>
                        <dd>{{ $school->description }}</dd>
                    </div>
                    @endif
                </dl>
            </section>
        @elseif ($panel === 'documents')
            <section>
                <h3 class="school-section-header">{{ __('messages.documents') }}</h3>
                <x-documents-school-table :documents="$documents" :school_id="$school_id" />

                @if (Auth::user()->getMode() == 'Edit')
                    <div class="school-upload-container">
                        <form action="{{ route('document.store', $school_id) }}" class="school-document-form nice-form nice-form--embedded" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="school-document-form__grid">
                                <div class="form-group">
                                    <x-input-label for="document_description">{{ __('messages.document_description') }}</x-input-label>
                                    <x-text-input type="text" name="description" id="document_description" placeholder="{{ __('messages.document_description') }}" />
                                    <x-input-error :messages="$errors->get('description')" />
                                </div>
                                <div class="form-group school-document-form__year">
                                    <x-input-label for="document_year">{{ __('messages.year') }}</x-input-label>
                                    <x-text-input type="text" name="year" id="document_year" value="{{ date('Y') }}" maxlength="4" inputmode="numeric" autocomplete="off" />
                                    <x-input-error :messages="$errors->get('year')" />
                                </div>
                                <div class="form-group school-document-form__file">
                                    <x-input-label for="document_file">{{ __('messages.file') }}</x-input-label>
                                    <input type="file" class="form-input school-document-form__file-input" name="document" id="document_file">
                                    <x-input-error :messages="$errors->get('document')" />
                                </div>
                            </div>
                            <div class="form-actions">
                                <x-button-primary type="submit">{{ __('messages.upload') }}</x-button-primary>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        @endif
    </div>

    <x-confirm-delete-modal
        name="confirm-document-delete"
        store="documentDelete"
        entity="document"
        :hints="[['field' => 'description', 'label' => __('messages.description')]]"
    />

    <x-confirm-delete-modal
        name="confirm-group-delete"
        store="groupDelete"
        entity="group"
        :hints="[['field' => 'name', 'label' => __('messages.name')]]"
    />
</x-app-layout>
