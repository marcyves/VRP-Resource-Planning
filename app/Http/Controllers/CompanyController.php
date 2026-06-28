<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function show()
    {
        $company = Auth::user()->company;
        $company->load(['contactUser', 'billingBankAccount.bank']);

        return view('company.show', compact('company'));
    }

    public function edit()
    {
        $this->authorizeCompanyEditor();

        $company = Auth::user()->company;
        $company->load('billingBankAccount.bank');
        $companyUsers = $this->companyUsersQuery()->get();
        $bankAccounts = BankAccount::where('company_id', $company->id)
            ->with('bank')
            ->where('active', true)
            ->orderBy('account_number')
            ->get();

        return view('company.edit', compact('company', 'companyUsers', 'bankAccounts'));
    }

    public function update(Request $request)
    {
        $this->authorizeCompanyEditor();

        $company = Auth::user()->company;

        $validated = $request->validate([
            'terminology_profile' => ['required', Rule::in(Company::terminologyProfileValues())],
            'siren' => ['nullable', 'digits:9'],
            'siret' => ['nullable', 'digits:14'],
            'vat_number' => ['nullable', 'string', 'max:20'],
            'legal_form' => ['nullable', 'string', 'max:255'],
            'share_capital' => ['nullable', 'string', 'max:50'],
            'contact_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('company_id', $company->id)),
            ],
            'billing_bank_account_id' => [
                'nullable',
                Rule::exists('bank_accounts', 'id')->where(fn ($query) => $query->where('company_id', $company->id)),
            ],
        ]);

        $company->fill(collect($validated)->except('contact_user_id')->all());
        $company->billing_bank_account_id = $validated['billing_bank_account_id'] ?? null;

        $contactUser = ! empty($validated['contact_user_id'])
            ? $this->companyUsersQuery()->find($validated['contact_user_id'])
            : null;

        $company->syncContactFromUser($contactUser);
        $company->save();

        session()->flash('success', __('messages.company_updated'));

        return redirect()->route('company.show');
    }

    private function companyUsersQuery()
    {
        return User::query()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name');
    }

    private function authorizeCompanyEditor(): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && ! $user->isEditor()) {
            abort(403);
        }
    }
}
