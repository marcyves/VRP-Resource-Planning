<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CompanyContactSyncTest extends TestCase
{
    public function test_sync_contact_from_user_copies_contact_fields(): void
    {
        $company = new Company;
        $user = new User([
            'email' => 'marc@example.com',
            'phone' => '0600000000',
            'website' => 'https://example.com',
        ]);
        $user->id = 7;

        $company->syncContactFromUser($user);

        $this->assertSame(7, $company->contact_user_id);
        $this->assertSame('marc@example.com', $company->email);
        $this->assertSame('0600000000', $company->phone);
        $this->assertSame('https://example.com', $company->website);
    }

    public function test_sync_contact_from_null_clears_contact_user_id(): void
    {
        $company = new Company(['contact_user_id' => 3, 'email' => 'old@example.com']);

        $company->syncContactFromUser(null);

        $this->assertNull($company->contact_user_id);
        $this->assertSame('old@example.com', $company->email);
    }
}
