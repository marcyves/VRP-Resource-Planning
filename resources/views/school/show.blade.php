<x-app-layout>
    @push('styles')
    @vite(['resources/css/schools.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.school_details') }}</h2>
    </x-slot>

    <div class="school-details-grid">
        <section class="glass-background">
            @php
            $school_name = $school->name;
            $school_id = $school->id;
            @endphp
            <article>
                <x-school-header :school_name="$school_name" :school_id="$school_id" />
                <x-course-table :courses="$courses" :school_name="$school_name" :school_id="$school_id" />
            </article>
        </section>

        <section class="glass-background">
            <h3 class="school-section-header">{{ __('messages.address') }}</h3>
            <div class="school-address-box">
                <div class="address-content">
                    {{ $school->address }}<br>
                    {{ $school->city }}, {{ $school->zip }}<br>
                    {{ $school->country }}<br>
                    @if($school->phone)
                    {{ __('messages.phone') }}: {{ $school->phone }}<br>
                    @endif
                </div>
            </div>
        </section>

        <section class="glass-background">
            <h3 class="school-section-header">Invoices</h3>
            <div class="bills">
                <x-table-invoices :invoices="$invoices" />
            </div>
        </section>

        <section class="glass-background">
            <h3 class="school-section-header">Documents</h3>
            <x-documents-school-table :documents="$documents" :school_id="$school_id" />

            <div class="school-upload-container">
                <form action="{{route('document.store', $school_id)}}" class="school-upload-form glass-background-solid" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-inputs">
                        <div class="school-form-input">
                            <x-text-input type="text" name="description" id="desc" placeholder="Document Description" />
                            <x-input-error :messages="$errors->get('description')" />
                        </div>
                        <div class="school-form-input">
                            <x-text-input type="text" name="year" id="year" value="{{date('Y')}}" />
                            <x-input-error :messages="$errors->get('year')" />
                        </div>
                    </div>

                    <div class="upload-actions">
                        <input type="file" class="form-input" name="document">
                        <x-input-error :messages="$errors->get('document')" />
                        <x-button-primary type="submit">Upload</x-button-primary>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <x-modal name="smallModal" focusable>
        <div class="p-6">
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