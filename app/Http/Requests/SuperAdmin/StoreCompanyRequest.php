<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'bill_prefix' => ['required', 'string', 'max:10', 'alpha_num', 'unique:companies,bill_prefix'],
            'terminology_profile' => ['required', Rule::in(Company::terminologyProfileValues())],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_name' => __('messages.super_admin_company_name'),
            'bill_prefix' => __('messages.super_admin_bill_prefix'),
            'terminology_profile' => __('messages.terminology_profile'),
            'admin_name' => __('messages.super_admin_admin_name'),
            'admin_email' => __('messages.super_admin_admin_email'),
            'admin_password' => __('messages.password'),
        ];
    }
}
