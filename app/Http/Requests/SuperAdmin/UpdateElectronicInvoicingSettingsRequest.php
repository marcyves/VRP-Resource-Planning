<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectronicInvoicingSettingsRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'superpdp_webhook_secret',
    ];

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
            'superpdp_webhook_secret' => ['nullable', 'string', 'min:8', 'max:512'],
            'clear_superpdp_webhook_secret' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'superpdp_webhook_secret' => __('messages.super_admin_webhook_secret'),
        ];
    }
}
