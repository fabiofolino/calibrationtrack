<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin',
        ]);
    }

    public function test_two_factor_auth_settings_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.two-factor-auth'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Profile/TwoFactorAuth')
                ->has('twoFactorEnabled')
                ->has('recoveryCodes')
                ->has('qrCodeSvg')
        );
    }

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.two-factor-auth.enable'), [
                'password' => 'password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNotNull($this->user->two_factor_secret);
        $this->assertNotNull($this->user->two_factor_recovery_codes);
    }

    public function test_user_can_confirm_two_factor_authentication(): void
    {
        // First enable 2FA
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
        ])->save();

        // Mock the 2FA provider to always return true for verification
        $this->mock(TwoFactorAuthenticationProvider::class)
            ->shouldReceive('verify')
            ->once()
            ->with('test-secret', '123456')
            ->andReturn(true);

        $response = $this->actingAs($this->user)
            ->post(route('profile.two-factor-auth.confirm'), [
                'code' => '123456',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNotNull($this->user->two_factor_confirmed_at);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        // Enable 2FA first
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($this->user)
            ->delete(route('profile.two-factor-auth.disable'), [
                'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNull($this->user->two_factor_secret);
        $this->assertNull($this->user->two_factor_recovery_codes);
        $this->assertNull($this->user->two_factor_confirmed_at);
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        // Enable 2FA first
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['old-code1', 'old-code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $oldCodes = $this->user->two_factor_recovery_codes;

        $response = $this->actingAs($this->user)
            ->post(route('profile.two-factor-auth.recovery-codes'), [
                'password' => 'password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNotEquals($oldCodes, $this->user->two_factor_recovery_codes);
    }

    public function test_invalid_password_prevents_enabling_two_factor(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.two-factor-auth.enable'), [
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors(['password']);

        $this->user->refresh();
        $this->assertNull($this->user->two_factor_secret);
    }

    public function test_invalid_code_prevents_confirming_two_factor(): void
    {
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
        ])->save();

        // Mock the 2FA provider to return false for verification
        $this->mock(TwoFactorAuthenticationProvider::class)
            ->shouldReceive('verify')
            ->once()
            ->with('test-secret', '000000')
            ->andReturn(false);

        $response = $this->actingAs($this->user)
            ->post(route('profile.two-factor-auth.confirm'), [
                'code' => '000000',
            ]);

        $response->assertSessionHasErrors(['code']);

        $this->user->refresh();
        $this->assertNull($this->user->two_factor_confirmed_at);
    }
}
