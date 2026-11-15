<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_can_be_rendered(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee(e(__('messages.landing_title')), false)
            ->assertSee(__('messages.landing_skip_content'), false)
            ->assertSee('btn btn-primary', false)
            ->assertSee('marketing-brand__name', false)
            ->assertSee('images/VRP-login.jpg', false)
            ->assertSee('images/landing-agenda.jpg', false)
            ->assertSee('images/landing-treasury.jpg', false)
            ->assertDontSee('marketing-brand__logo', false)
            ->assertDontSee(__('messages.landing_eyebrow'), false);
    }

    public function test_welcome_page_starts_with_illustration_hero(): void
    {
        $html = $this->get(route('welcome'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="main-content"[^>]*>\s*<section class="marketing-hero"/',
            $html
        );

        preg_match('/<section class="marketing-hero".*?(?=<section )/s', $html, $hero);
        $this->assertNotEmpty($hero);
        $this->assertStringContainsString('images/VRP-login.jpg', $hero[0]);
        $this->assertStringContainsString(e(__('messages.landing_title')), $hero[0]);
        $this->assertStringContainsString(__('messages.landing_trial_cta'), $hero[0]);
        $this->assertStringNotContainsString('landing-agenda.jpg', $hero[0]);
        $this->assertStringNotContainsString('landing-treasury.jpg', $hero[0]);
    }

    public function test_root_url_shows_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.landing_request_access'), false);
    }

    public function test_welcome_page_uses_commercial_structure(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('id="fonctionnalites"', false)
            ->assertSee('id="captures"', false)
            ->assertSee('id="tarifs"', false)
            ->assertSee('id="a-propos"', false)
            ->assertSee(__('messages.landing_nav_features'), false)
            ->assertSee(__('messages.landing_nav_pricing'), false)
            ->assertSee(__('messages.landing_nav_about'), false)
            ->assertSee(__('messages.landing_trial_cta'), false)
            ->assertSee(__('messages.landing_discover_features'), false)
            ->assertSee(__('messages.landing_stage_agenda'), false)
            ->assertSee(__('messages.landing_stage_treasury'), false)
            ->assertSee(e(__('messages.landing_screens_title')), false)
            ->assertSee(__('messages.landing_compare_title'), false)
            ->assertSee(__('messages.landing_steps_title'), false)
            ->assertSeeText(__('messages.landing_feature_planning_title'))
            ->assertSeeText(__('messages.landing_feature_workload_title'))
            ->assertSeeText(__('messages.landing_feature_billing_title'))
            ->assertSeeText(__('messages.landing_feature_treasury_title'))
            ->assertSeeText(__('messages.landing_feature_profiles_title'))
            ->assertSeeText(__('messages.landing_feature_secure_title'))
            ->assertSee('marketing-features__grid', false)
            ->assertSee('marketing-pricing__offer', false)
            ->assertSee('data-landing-cta="register"', false)
            ->assertSee('data-landing-cta="login"', false)
            ->assertSee('data-landing-cta="pricing"', false)
            ->assertSee(e(__('messages.landing_pricing_title')), false)
            ->assertSee(__('messages.landing_pricing_trial_price'), false)
            ->assertSee(__('messages.landing_pricing_monthly_price'), false)
            ->assertSee(__('messages.landing_pricing_cta'), false)
            ->assertSee(__('messages.landing_about_title'), false)
            ->assertSee(e(__('messages.landing_about_lead')), false)
            ->assertSee(route('login', absolute: false), false)
            ->assertSee(route('account-request.create', absolute: false), false)
            ->assertSee(route('marketing.product', absolute: false), false)
            ->assertSee(route('marketing.pricing', absolute: false), false)
            ->assertSee(route('marketing.about', absolute: false), false)
            ->assertSee(route('marketing.legal', absolute: false), false)
            ->assertDontSee('href="'.url('/register').'"', false)
            ->assertDontSee('href="/register"', false);
    }

    public function test_public_marketing_routes_can_be_rendered(): void
    {
        $this->get(route('marketing.product'))
            ->assertOk()
            ->assertSee(__('messages.landing_product_title'), false)
            ->assertSee(__('messages.landing_module_billing_title'), false)
            ->assertSee('images/landing-agenda.jpg', false)
            ->assertSee('data-landing-cta="register"', false);

        $this->get(route('marketing.pricing'))
            ->assertOk()
            ->assertSee(e(__('messages.landing_pricing_title')), false)
            ->assertSee(__('messages.landing_faq_start_q'), false)
            ->assertSee('data-landing-cta="pricing"', false);

        $this->get(route('marketing.about'))
            ->assertOk()
            ->assertSee(__('messages.landing_about_editor_title'), false)
            ->assertSee('XDM Consulting', false);

        $this->get(route('marketing.legal'))
            ->assertOk()
            ->assertSee(__('messages.landing_legal_title'), false)
            ->assertSee(__('messages.landing_legal_privacy_title'), false);
    }

    public function test_welcome_page_copy_is_translated_for_supported_locales(): void
    {
        foreach (['fr', 'en', 'it'] as $locale) {
            $this->app->setLocale($locale);

            $this->assertNotSame('messages.landing_title', __('messages.landing_title'));
            $this->assertNotSame('messages.landing_trial_cta', __('messages.landing_trial_cta'));
            $this->assertNotSame('messages.landing_pricing_cta', __('messages.landing_pricing_cta'));
            $this->assertNotSame('messages.landing_screens_title', __('messages.landing_screens_title'));
            $this->assertStringContainsString('0 €', __('messages.landing_pricing_trial_price'));
            $this->assertStringContainsString('10 €', __('messages.landing_pricing_monthly_price'));

            $this->get(route('welcome'))
                ->assertOk()
                ->assertSee(e(__('messages.landing_title')), false)
                ->assertSee(e(__('messages.landing_pricing_title')), false);
        }
    }

    public function test_landing_banner_switches_language(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee(route('locale.switch', 'fr'), false)
            ->assertSee(route('locale.switch', 'en'), false)
            ->assertSee(route('locale.switch', 'it'), false)
            ->assertSee(e(__('messages.landing_title')), false);

        $this->get(route('locale.switch', 'en'))
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');

        $this->withSession(['locale' => 'en'])
            ->get(route('welcome'))
            ->assertOk()
            ->assertSee('Invoice the work already on the agenda', false);

        $this->withSession(['locale' => 'it'])
            ->get(route('welcome'))
            ->assertOk()
            ->assertSee(e('Fatturate il lavoro già sull\'agenda'), false);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('messages.landing_no_account'), false);
    }

    public function test_account_request_page_can_be_rendered(): void
    {
        $this->get(route('account-request.create'))
            ->assertOk()
            ->assertSee(__('messages.landing_request_submit'), false);
    }

    public function test_account_request_can_be_submitted(): void
    {
        Mail::fake();

        config(['vrp.account_request_email' => 'admin@example.test']);

        $response = $this->post(route('account-request.store'), [
            'company_name' => 'Acme Formation',
            'contact_name' => 'Alice Martin',
            'email' => 'alice@acme.test',
            'phone' => '0601020304',
            'terminology_profile' => 'education',
            'message' => 'Besoin d\'un essai.',
        ]);

        $response
            ->assertRedirect(route('account-request.create'))
            ->assertSessionHas('status');

        Mail::assertSent(\App\Mail\AccountRequestMail::class, function ($mail) {
            return $mail->payload['company_name'] === 'Acme Formation'
                && $mail->payload['email'] === 'alice@acme.test';
        });
    }
}
