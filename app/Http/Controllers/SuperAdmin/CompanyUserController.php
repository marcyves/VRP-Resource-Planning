<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreCompanyUserRequest;
use App\Models\Company;
use App\Services\CompanyUserProvisioner;
use Illuminate\Http\RedirectResponse;

class CompanyUserController extends Controller
{
    public function store(
        StoreCompanyUserRequest $request,
        Company $company,
        CompanyUserProvisioner $provisioner
    ): RedirectResponse {
        $provisioner->provision($company, $request->validated());

        session()->flash('success', __('messages.super_admin_user_created'));

        return redirect()->route('super-admin.companies.show', $company);
    }
}
