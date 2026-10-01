<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\LoginEvent;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            StatusSeeder::class,
        ]);

        Http::preventStrayRequests();
    }

    public function test_successful_login_is_recorded(): void
    {
        $user = $this->makeCompanyAdmin();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'username' => $user->email,
            'success' => 1,
            'locked_out' => 0,
        ]);

        $event = LoginEvent::query()->first();
        $this->assertNotNull($event);
        $this->assertNotEmpty($event->ip);
        $this->assertNotNull($event->occurred_at);
        $this->assertSame(LoginEvent::GEO_LOCAL, $event->geo_label);
    }

    public function test_failed_login_for_known_user_is_recorded_with_company(): void
    {
        $user = $this->makeCompanyAdmin();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'username' => $user->email,
            'success' => 0,
            'locked_out' => 0,
        ]);
    }

    public function test_failed_login_for_unknown_user_is_stored_without_company(): void
    {
        $this->post('/login', [
            'email' => 'ghost@example.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseHas('login_events', [
            'user_id' => null,
            'company_id' => null,
            'username' => 'ghost@example.test',
            'success' => 0,
        ]);
    }

    public function test_geo_failure_does_not_block_login(): void
    {
        config(['login_stats.geo.http_enabled' => true]);
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('geo timeout');
        });

        $user = $this->makeCompanyAdmin();

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'success' => 1,
            'geo_label' => null,
            'ip' => '8.8.8.8',
        ]);
    }

    public function test_company_admin_can_view_login_stats(): void
    {
        $admin = $this->makeCompanyAdmin();
        LoginEvent::factory()->successful()->create([
            'company_id' => $admin->company_id,
            'user_id' => $admin->id,
            'username' => $admin->email,
            'ip' => '203.0.113.10',
            'geo_label' => 'Paris, France',
        ]);

        $this->actingAs($admin)
            ->get(route('login-stats.index'))
            ->assertOk()
            ->assertSee(__('messages.login_stats'), false)
            ->assertSee($admin->email, false)
            ->assertSee('203.0.113.10', false)
            ->assertSee('Paris, France', false)
            ->assertSee(__('messages.login_stats_unique'), false);
    }

    public function test_company_admin_does_not_see_other_company_or_unscoped_events(): void
    {
        $admin = $this->makeCompanyAdmin();
        $otherCompany = Company::factory()->create();

        LoginEvent::factory()->failed()->create([
            'company_id' => $otherCompany->id,
            'username' => 'other@example.test',
            'ip' => '198.51.100.9',
        ]);
        LoginEvent::factory()->failed()->create([
            'company_id' => null,
            'username' => 'ghost@example.test',
            'ip' => '198.51.100.8',
        ]);
        LoginEvent::factory()->successful()->create([
            'company_id' => $admin->company_id,
            'username' => $admin->email,
            'ip' => '203.0.113.10',
        ]);

        $this->actingAs($admin)
            ->get(route('login-stats.index'))
            ->assertOk()
            ->assertSee($admin->email, false)
            ->assertDontSee('other@example.test', false)
            ->assertDontSee('ghost@example.test', false);
    }

    public function test_super_admin_sees_all_events_including_unscoped_failures(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $admin = $this->makeCompanyAdmin();

        LoginEvent::factory()->failed()->create([
            'company_id' => null,
            'username' => 'ghost@example.test',
            'ip' => '198.51.100.8',
        ]);
        LoginEvent::factory()->successful()->create([
            'company_id' => $admin->company_id,
            'username' => $admin->email,
            'ip' => '203.0.113.10',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.login-stats.index'))
            ->assertOk()
            ->assertSee('ghost@example.test', false)
            ->assertSee($admin->email, false)
            ->assertSee(__('messages.login_stats_company_none'), false);
    }

    public function test_editor_cannot_view_login_stats(): void
    {
        $editor = User::factory()->create([
            'status_id' => Status::EDITOR,
        ]);

        $this->actingAs($editor)
            ->get(route('login-stats.index'))
            ->assertForbidden();
    }

    public function test_reader_cannot_view_login_stats(): void
    {
        $reader = User::factory()->create([
            'status_id' => Status::READER,
        ]);

        $this->actingAs($reader)
            ->get(route('login-stats.index'))
            ->assertForbidden();
    }

    public function test_company_admin_cannot_access_super_admin_login_stats(): void
    {
        $admin = $this->makeCompanyAdmin();

        $this->actingAs($admin)
            ->get(route('super-admin.login-stats.index'))
            ->assertForbidden();
    }

    public function test_outcome_filter_limits_the_table(): void
    {
        $admin = $this->makeCompanyAdmin();

        LoginEvent::factory()->successful()->create([
            'company_id' => $admin->company_id,
            'username' => 'ok@example.test',
            'ip' => '203.0.113.10',
        ]);
        LoginEvent::factory()->failed()->create([
            'company_id' => $admin->company_id,
            'username' => 'bad@example.test',
            'ip' => '203.0.113.11',
        ]);

        $this->actingAs($admin)
            ->get(route('login-stats.index', ['outcome' => 'failed']))
            ->assertOk()
            ->assertSee('bad@example.test', false)
            ->assertDontSee('ok@example.test', false);
    }

    private function makeCompanyAdmin(?Company $company = null): User
    {
        $company ??= Company::factory()->create();

        return User::factory()->create([
            'company_id' => $company->id,
            'status_id' => Status::ADMIN,
        ]);
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'company_id' => null,
            'status_id' => Status::superAdminId(),
        ]);
    }
}
