<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.school_details') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <div class="school-details-grid">
        <section>
            @php
            $school_name = $school->name;
            $school_id = $school->id;
            @endphp
            <article>
                <x-school-header :school_name="$school_name" :school_id="$school_id" />
                <x-course-table :courses="$courses" :school_name="$school_name" :school_id="$school_id" />
            </article>
        </section>

        <section>
            <h3 class="school-section-header">{{ __('messages.address') }}</h3>
            <div class="school-address-box">
                <div class="address-content">
                    {{ $school->address }}<br>
                    {{ $school->city }}, {{ $school->zip }}<br>
                    {{ $school->country }}<br>
                    @if($school->phone)
                    {{ __('messages.phone') }}: {{ $school->phone }}<br>
                    @endif
                    @if($school->siren)
                    {{ __('messages.siren') }}: {{ $school->siren }}<br>
                    @endif
                    @if($school->siret)
                    {{ __('messages.siret') }}: {{ $school->siret }}<br>
                    @endif
                    @if($school->vat_number)
                    {{ __('messages.vat_number') }}: {{ $school->vat_number }}<br>
                    @endif
                </div>
            </div>
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
    </div>

    <x-modal name="smallModal" focusable>
        <div class="profile-modal-form">
            <div id="smallBody">
                <!-- Ajax content -->
            </div>
        </div>
    </x-modal>

    <script>
        // Use Alpine to show x-modal if needed or standard JS
        $(document).on('click', '#smallButton', function(event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $.ajax({
                url: href,
                beforeSend: function() {
                    $('#loader').show();
                },
                success: function(result) {
                    $('#smallBody').html(result);
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'smallModal'
                    }));
                },
                complete: function() {
                    $('#loader').hide();
                },
                error: function(jqXHR, testStatus, error) {
                    console.log(error);
                    alert("Page " + href + " cannot open. Error:" + error);
                    $('#loader').hide();
                }
            });
        });
    </script>
</x-app-layout>