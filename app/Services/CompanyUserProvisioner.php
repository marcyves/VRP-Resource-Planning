<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CompanyUserProvisioner
{
    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     status_id: int,
     * }  $data
     */
    public function provision(Company $company, array $data): User
    {
        $mode = in_array((int) $data['status_id'], [Status::ADMIN, Status::EDITOR], true)
            ? 'Edit'
            : 'Browse';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'company_id' => $company->id,
            'status_id' => (int) $data['status_id'],
            'mode' => $mode,
            'email_verified_at' => now(),
        ]);

        if ($company->contact_user_id === null && (int) $data['status_id'] === Status::ADMIN) {
            $company->syncContactFromUser($user);
            $company->save();
        }

        return $user;
    }
}
