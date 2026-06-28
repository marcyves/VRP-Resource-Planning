<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyDeleter
{
    public function delete(Company $company): void
    {
        DB::transaction(function () use ($company) {
            $companyId = $company->id;
            $billPrefix = $company->bill_prefix;

            DB::table('companies')
                ->where('id', $companyId)
                ->update([
                    'contact_user_id' => null,
                    'billing_bank_account_id' => null,
                ]);

            $schoolIds = DB::table('schools')->where('company_id', $companyId)->pluck('id');
            foreach ($schoolIds as $schoolId) {
                $this->deleteSchoolTree((int) $schoolId);
            }

            $groupIds = DB::table('groups')->where('company_id', $companyId)->pluck('id');
            $courseIds = DB::table('courses')
                ->join('schools', 'schools.id', '=', 'courses.school_id')
                ->where('schools.company_id', $companyId)
                ->pluck('courses.id');

            if ($groupIds->isNotEmpty() || $courseIds->isNotEmpty()) {
                DB::table('plannings')
                    ->where(function ($query) use ($groupIds, $courseIds) {
                        if ($groupIds->isNotEmpty()) {
                            $query->whereIn('group_id', $groupIds);
                        }
                        if ($courseIds->isNotEmpty()) {
                            $query->orWhereIn('course_id', $courseIds);
                        }
                    })
                    ->delete();
            }

            if ($groupIds->isNotEmpty()) {
                DB::table('group_course')->whereIn('group_id', $groupIds)->delete();
                DB::table('groups')->where('company_id', $companyId)->delete();
            }

            if ($billPrefix !== '') {
                DB::table('plannings')
                    ->where('invoice_id', 'like', $billPrefix.'%')
                    ->update(['invoice_id' => '']);
            }

            DB::table('invoices')->where('company_id', $companyId)->delete();
            DB::table('programs')->where('company_id', $companyId)->delete();

            $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id');
            if ($userIds->isNotEmpty()) {
                DB::table('school_user')->whereIn('user_id', $userIds)->delete();
                DB::table('users')->where('company_id', $companyId)->delete();
            }

            Company::query()->whereKey($companyId)->delete();
        });
    }

    private function deleteSchoolTree(int $schoolId): void
    {
        DB::table('calendar_mappings')->where('school_id', $schoolId)->delete();
        DB::table('calendar_sources')->where('school_id', $schoolId)->delete();
        DB::table('documents')->where('school_id', $schoolId)->delete();
        DB::table('school_user')->where('school_id', $schoolId)->delete();

        $courseIds = DB::table('courses')->where('school_id', $schoolId)->pluck('id');
        if ($courseIds->isNotEmpty()) {
            DB::table('group_course')->whereIn('course_id', $courseIds)->delete();
            DB::table('plannings')->whereIn('course_id', $courseIds)->delete();
            DB::table('courses')->where('school_id', $schoolId)->delete();
        }

        DB::table('schools')->where('id', $schoolId)->delete();
    }
}
