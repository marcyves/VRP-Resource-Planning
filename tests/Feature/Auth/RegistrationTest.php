<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        config(['vrp.allow_registration' => true]);

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_is_disabled_by_default(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }

    public function test_new_users_can_register(): void
    {
        config(['vrp.allow_registration' => true]);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
