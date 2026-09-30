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
            ->assertSee(__('messages.landing_title'), false)
            ->assertSee(__('messages.landing_skip_content'), false)
            ->assertSee('btn btn-primary', false)
            ->assertDontSee('marketing-brand__logo', false)
            ->assertDontSee(__('messages.landing_eyebrow'), false)
            ->assertDontSee('images/VRP.jpeg', false);
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
            ->assertSee('id="tarifs"', false)
            ->assertSee('id="a-propos"', false)
            ->assertSee(__('messages.landing_nav_features'), false)
            ->assertSee(__('messages.landing_nav_pricing'), false)
            ->assertSee(__('messages.landing_nav_about'), false)
            ->assertSee(__('messages.landing_trial_cta'), false)
            ->assertSee(__('messages.landing_discover_features'), false)
            ->assertSee(__('messages.landing_pricing_title'), false)
            ->assertSee(__('messages.landing_pricing_trial_price'), false)
            ->assertSee(__('messages.landing_pricing_monthly_price'), false)
            ->assertSee(__('messages.landing_pricing_cta'), false)
            ->assertSee(__('messages.landing_about_title'), false)
            ->assertSee(__('messages.landing_about_lead'), false)
            ->assertSee(route('login', absolute: false), false)
            ->assertSee(route('account-request.create', absolute: false), false)
            ->assertDontSee('href="'.url('/register').'"', false)
            ->assertDontSee('href="/register"', false);
    }

    public function test_welcome_page_copy_is_translated_for_supported_locales(): void
    {
        foreach (['fr', 'en', 'it'] as $locale) {
            $this->app->setLocale($locale);

            $this->assertNotSame('messages.landing_title', __('messages.landing_title'));
            $this->assertNotSame('messages.landing_trial_cta', __('messages.landing_trial_cta'));
            $this->assertNotSame('messages.landing_pricing_cta', __('messages.landing_pricing_cta'));
            $this->assertNotSame('messages.landing_about_lead', __('messages.landing_about_lead'));
            $this->assertStringContainsString('0 €', __('messages.landing_pricing_trial_price'));
            $this->assertStringContainsString('10 €', __('messages.landing_pricing_monthly_price'));

            $this->get(route('welcome'))
                ->assertOk()
                ->assertSee(__('messages.landing_title'), false)
                ->assertSee(__('messages.landing_pricing_title'), false);
        }
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
