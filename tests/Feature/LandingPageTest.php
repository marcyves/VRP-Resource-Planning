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
            ->assertSee(__('messages.landing_title'), false);
    }

    public function test_root_url_shows_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.landing_request_access'), false);
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
