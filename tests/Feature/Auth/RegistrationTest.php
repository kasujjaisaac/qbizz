<?php

namespace Tests\Feature\Auth;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_can_join_an_existing_workspace_with_an_invite_code(): void
    {
        $owner = User::factory()->create();
        $businessProfile = BusinessProfile::create([
            'user_id' => $owner->id,
            'business_name' => 'Acme Shared Workspace',
            'contact_email' => 'hello@acme.test',
            'phone' => '+256700000000',
            'address_line_1' => 'Plot 12 Market Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'postal_code' => '256',
            'country' => 'Uganda',
            'logo_path' => 'business-logos/logo.png',
            'setup_completed_at' => now(),
            'team_invite_code' => 'TEAM-SPACE123',
        ]);

        $owner->forceFill([
            'business_profile_id' => $businessProfile->id,
        ])->save();

        $response = $this->post('/register', [
            'name' => 'Teammate User',
            'email' => 'teammate@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite_code' => 'TEAM-SPACE123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $teammate = User::query()->where('email', 'teammate@example.com')->firstOrFail();

        $this->assertSame($businessProfile->id, $teammate->business_profile_id);
        $this->actingAs($teammate)->get(route('dashboard'))->assertOk();
    }
}
