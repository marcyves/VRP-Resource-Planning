<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreCompanyRequest;
use App\Http\Requests\SuperAdmin\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Status;
use App\Services\CompanyDeleter;
use App\Services\CompanyProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::query()
            ->withCount('users')
            ->with(['users' => fn ($query) => $query->where('status_id', Status::ADMIN)->orderBy('id')])
            ->orderBy('name')
            ->get();

        return view('super-admin.companies.index', compact('companies'));
    }

    public function show(Company $company): View
    {
        $company->load(['users' => fn ($query) => $query->orderBy('name')]);

        $roleOptions = [
            Status::ADMIN => __('messages.super_admin_role_admin'),
            Status::EDITOR => __('messages.super_admin_role_editor'),
            Status::READER => __('messages.super_admin_role_reader'),
        ];

        return view('super-admin.companies.show', compact('company', 'roleOptions'));
    }

    public function create(): View
    {
        return view('super-admin.companies.create');
    }

    public function store(StoreCompanyRequest $request, CompanyProvisioner $provisioner): RedirectResponse
    {
        $provisioner->provision($request->validated());

        session()->flash('success', __('messages.super_admin_company_created'));

        return redirect()->route('super-admin.companies.index');
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        session()->flash('success', __('messages.super_admin_company_updated'));

        return redirect()->route('super-admin.companies.show', $company);
    }

    public function destroy(Company $company, CompanyDeleter $deleter): RedirectResponse
    {
        $name = $company->name;
        $deleter->delete($company);

        session()->flash('success', __('messages.super_admin_company_deleted', ['name' => $name]));

        return redirect()->route('super-admin.companies.index');
    }
}
