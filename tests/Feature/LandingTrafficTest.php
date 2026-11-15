<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\LandingVisit;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingTrafficTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([StatusSeeder::class]);
        Http::preventStrayRequests();
    }

    public function test_landing_visit_is_recorded_with_sales_fields(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'User-Agent' => self::CHROME_UA,
                'Referer' => 'https://www.google.com/search?q=vrp+plan+formation',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
            ])
            ->get('/?utm_source=google&utm_medium=organic&utm_campaign=brand&utm_content=hero&utm_term=vrp')
            ->assertOk();

        $this->assertDatabaseCount('landing_visits', 1);
        $visit = LandingVisit::query()->orderByDesc('id')->first();
        $this->assertNotNull($visit);
        $this->assertSame('landing', $visit->page);
        $this->assertSame('fr', $visit->locale);
        $this->assertSame('fr-FR,fr;q=0.9,en;q=0.8', $visit->accept_language);
        $this->assertSame('Windows', $visit->os);
        $this->assertSame('desktop', $visit->device_type);
        $this->assertSame('Chrome', $visit->browser);
        $this->assertSame('www.google.com', $visit->referer_host);
        $this->assertSame('https://www.google.com/search?q=vrp+plan+formation', $visit->referer_url);
        $this->assertSame('vrp', $visit->keywords);
        $this->assertSame('google', $visit->utm_source);
        $this->assertSame('organic', $visit->utm_medium);
        $this->assertSame('brand', $visit->utm_campaign);
        $this->assertSame('hero', $visit->utm_content);
        $this->assertSame('vrp', $visit->utm_term);
        $this->assertSame('/', $visit->landing_path);
        $this->assertStringContainsString('utm_source=google', (string) $visit->landing_query);
        $this->assertFalse($visit->is_returning);
        $this->assertNull($visit->cta);
        $this->assertNull($visit->country);
        $this->assertNotEmpty($visit->ip_hash);
        $this->assertNotSame('127.0.0.1', $visit->ip_hash);
        $this->assertTrue(Str::isUuid($visit->visitor_id));
        $this->assertNotNull($visit->visited_at);
    }

    public function test_keywords_are_taken_from_referer_when_landing_has_none(): void
    {
        $this->withHeaders([
            'User-Agent' => self::CHROME_UA,
            'Referer' => 'https://www.google.fr/search?q=logiciel+planning+formateur',
        ])->get('/')->assertOk();

        $this->assertSame('logiciel planning formateur', LandingVisit::query()->orderByDesc('id')->value('keywords'));
    }

    public function test_landing_locale_follows_guest_session(): void
    {
        $this->withSession(['locale' => 'it'])
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get('/')
            ->assertOk();

        $this->assertSame('it', LandingVisit::query()->orderByDesc('id')->value('locale'));
    }

    public function test_login_and_account_request_pages_are_recorded(): void
    {
        $visitor = (string) Str::uuid();

        $this->withCookie(config('landing_traffic.cookie'), $visitor)
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get(route('login'))
            ->assertOk();

        $this->withCookie(config('landing_traffic.cookie'), $visitor)
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get(route('account-request.create'))
            ->assertOk();

        $this->assertDatabaseHas('landing_visits', [
            'page' => 'login',
            'cta' => 'login',
            'visitor_id' => $visitor,
        ]);
        $this->assertDatabaseHas('landing_visits', [
            'page' => 'register',
            'cta' => 'register',
            'visitor_id' => $visitor,
        ]);
    }

    public function test_cta_endpoint_marks_latest_landing_visit(): void
    {
        $visitor = (string) Str::uuid();
        $visit = LandingVisit::factory()->create([
            'visitor_id' => $visitor,
            'page' => 'landing',
            'cta' => null,
        ]);

        $this->withCredentials()
            ->withCookie(config('landing_traffic.cookie'), $visitor)
            ->postJson(route('landing-visits.cta'), ['cta' => 'pricing'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame('pricing', $visit->fresh()->cta);
    }

    public function test_dnt_skips_recording(): void
    {
        $this->withHeaders([
            'User-Agent' => self::CHROME_UA,
            'DNT' => '1',
        ])->get('/')->assertOk();

        $this->assertDatabaseCount('landing_visits', 0);
    }

    public function test_bots_are_not_recorded(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->get('/')->assertOk();

        $this->assertDatabaseCount('landing_visits', 0);
    }

    public function test_authenticated_users_are_not_recorded_on_landing(): void
    {
        $admin = $this->makeCompanyAdmin();

        $this->actingAs($admin)
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get('/')
            ->assertRedirect();

        $this->assertDatabaseCount('landing_visits', 0);
    }

    public function test_returning_cookie_marks_visitor(): void
    {
        $visitor = (string) Str::uuid();

        $this->withCookie(config('landing_traffic.cookie'), $visitor)
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get('/')
            ->assertOk();

        $visit = LandingVisit::query()->orderByDesc('id')->first();
        $this->assertTrue((bool) $visit?->is_returning);
        $this->assertSame($visitor, $visit?->visitor_id);
    }

    public function test_super_admin_can_view_landing_traffic(): void
    {
        $visit = LandingVisit::factory()->create([
            'keywords' => 'logiciel planning',
            'referer_host' => 'www.google.com',
            'utm_source' => 'newsletter',
            'country' => 'France',
            'ip_hash' => hash('sha256', '203.0.113.10|secret'),
        ]);

        $this->actingAs($this->makeSuperAdmin())
            ->get(route('super-admin.landing-traffic.index'))
            ->assertOk()
            ->assertSee(__('messages.landing_traffic'), false)
            ->assertSee('logiciel planning', false)
            ->assertSee('www.google.com', false)
            ->assertSee('newsletter', false)
            ->assertSee('France', false)
            ->assertDontSee($visit->ip_hash, false)
            ->assertDontSee('203.0.113.10', false);
    }

    public function test_admin_page_displays_rome_time(): void
    {
        LandingVisit::factory()->create([
            'visited_at' => '2026-11-15 00:00:00',
            'keywords' => 'rome-time-marker',
        ]);

        $this->actingAs($this->makeSuperAdmin())
            ->get(route('super-admin.landing-traffic.index'))
            ->assertOk()
            ->assertSee('15/11/2026 01:00:00', false)
            ->assertSee('Europe/Rome', false);
    }

    public function test_filters_limit_the_table(): void
    {
        LandingVisit::factory()->create([
            'locale' => 'fr',
            'device_type' => 'desktop',
            'keywords' => 'keep-me',
            'referer_host' => 'www.google.com',
            'utm_source' => 'google',
        ]);
        LandingVisit::factory()->mobile()->create([
            'locale' => 'en',
            'keywords' => 'hide-me',
            'referer_host' => 'newsletter.example',
            'utm_source' => 'email',
        ]);

        $this->actingAs($this->makeSuperAdmin())
            ->get(route('super-admin.landing-traffic.index', [
                'locale' => 'fr',
                'device' => 'desktop',
                'referer_host' => 'google.com',
                'utm' => 'google',
            ]))
            ->assertOk()
            ->assertSee('keep-me', false)
            ->assertDontSee('hide-me', false);
    }

    public function test_company_admin_cannot_view_landing_traffic(): void
    {
        $this->actingAs($this->makeCompanyAdmin())
            ->get(route('super-admin.landing-traffic.index'))
            ->assertForbidden();
    }

    public function test_editor_cannot_view_landing_traffic(): void
    {
        $editor = User::factory()->create([
            'status_id' => Status::EDITOR,
        ]);

        $this->actingAs($editor)
            ->get(route('super-admin.landing-traffic.index'))
            ->assertForbidden();
    }

    public function test_geo_http_is_not_called_for_landing_visits(): void
    {
        Http::fake();

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->withHeaders(['User-Agent' => self::CHROME_UA])
            ->get('/')
            ->assertOk();

        $this->assertDatabaseCount('landing_visits', 1);
        Http::assertNothingSent();
        $this->assertNull(LandingVisit::query()->orderByDesc('id')->value('country'));
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
