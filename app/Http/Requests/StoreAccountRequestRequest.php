<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'terminology_profile' => ['nullable', Rule::in(Company::terminologyProfileValues())],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_name' => __('messages.landing_request_company'),
            'contact_name' => __('messages.landing_request_contact'),
            'email' => __('messages.email'),
            'phone' => __('messages.phone'),
            'terminology_profile' => __('messages.terminology_profile'),
            'message' => __('messages.landing_request_message'),
        ];
    }
}
