{{ __('messages.landing_request_mail_intro') }}

{{ __('messages.landing_request_company') }}: {{ $payload['company_name'] }}
{{ __('messages.landing_request_contact') }}: {{ $payload['contact_name'] }}
{{ __('messages.email') }}: {{ $payload['email'] }}
@if (! empty($payload['phone']))
{{ __('messages.phone') }}: {{ $payload['phone'] }}
@endif
@if (! empty($payload['terminology_profile']))
{{ __('messages.terminology_profile') }}: {{ __('messages.terminology_profile_' . $payload['terminology_profile']) }}
@endif
@if (! empty($payload['message']))
{{ __('messages.landing_request_message') }}:
{{ $payload['message'] }}
@endif
