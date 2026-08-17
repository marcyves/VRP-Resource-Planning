@props([
    'sessions' => '',
    'sessionLength' => '',
    'rate' => '',
    'rateBasis' => 'ttc',
])

@php
    $sessions = old('sessions', $sessions);
    $sessionLength = old('session_length', $sessionLength);
    $rate = old('rate', $rate);
    $rateBasis = old('rate_basis', $rateBasis);
@endphp

<div
    class="form-group course-volume"
    x-data="{
        sessions: @js((string) $sessions),
        sessionLength: @js((string) $sessionLength),
        parse(value) {
            const n = parseFloat(String(value).replace(',', '.'));
            return Number.isFinite(n) ? n : 0;
        },
        get totalHours() {
            const total = this.parse(this.sessions) * this.parse(this.sessionLength);
            if (!total) {
                return '—';
            }
            return Number.isInteger(total) ? String(total) : String(Math.round(total * 100) / 100).replace('.', ',');
        }
    }"
>
    <x-input-label for="sessions">{{ __('messages.sessions') }}</x-input-label>
    <div class="course-volume-line">
        <div class="course-form-row__cluster">
            <x-text-input
                type="text"
                inputmode="decimal"
                name="sessions"
                id="sessions"
                class="course-volume-line__input"
                x-model="sessions"
                value="{{ $sessions }}"
                aria-label="{{ __('messages.number_of_sessions') }}"
            />
            <span class="course-volume-line__text">{{ __('messages.sessions_of') }}</span>
            <x-text-input
                type="text"
                inputmode="decimal"
                name="session_length"
                id="session_length"
                class="course-volume-line__input"
                x-model="sessionLength"
                value="{{ $sessionLength }}"
                aria-label="{{ __('messages.session_length') }}"
            />
            <span class="course-volume-line__text">{{ __('messages.hours_unit') }}</span>
        </div>
        <div class="course-form-row__cluster">
            <x-text-input
                type="text"
                inputmode="decimal"
                name="rate"
                id="rate"
                class="course-volume-line__rate"
                maxlength="10"
                value="{{ $rate }}"
                placeholder="{{ __('messages.rate') }}"
                aria-label="{{ __('messages.rate') }}"
            />
            <div class="course-rate-basis" role="radiogroup" aria-label="{{ __('messages.rate_basis') }}">
                <label>
                    <input type="radio" name="rate_basis" value="ttc" @checked($rateBasis === 'ttc')>
                    {{ __('messages.amount_ttc') }}
                </label>
                <label>
                    <input type="radio" name="rate_basis" value="ht" @checked($rateBasis === 'ht')>
                    {{ __('messages.amount_ht') }}
                </label>
            </div>
        </div>
    </div>
    <p class="form-hint course-volume-total">
        {{ __('messages.total_time') }} :
        <span x-text="totalHours"></span> {{ __('messages.hours_initial') }}
    </p>
    <x-input-error :messages="$errors->get('sessions')" />
    <x-input-error :messages="$errors->get('session_length')" />
    <x-input-error :messages="$errors->get('rate')" />
</div>
