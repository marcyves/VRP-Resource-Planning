<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreCompanyUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status_id' => ['required', Rule::in([Status::ADMIN, Status::EDITOR, Status::READER])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('messages.name'),
            'email' => __('messages.email'),
            'password' => __('messages.password'),
            'status_id' => __('messages.super_admin_user_role'),
        ];
    }
}
