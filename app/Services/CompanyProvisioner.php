<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyProvisioner
{
    /**
     * @param  array{
     *     company_name: string,
     *     bill_prefix: string,
     *     terminology_profile: string,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     * }  $data
     * @return array{company: Company, admin: User}
     */
    public function provision(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data['company_name'],
                'bill_prefix' => strtoupper($data['bill_prefix']),
                'terminology_profile' => $data['terminology_profile'],
            ]);

            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'company_id' => $company->id,
                'status_id' => Status::ADMIN,
                'mode' => 'Edit',
                'email_verified_at' => now(),
            ]);

            $company->syncContactFromUser($admin);
            $company->save();

            return compact('company', 'admin');
        });
    }
}
