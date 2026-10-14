<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\User;
use Database\Seeders\GeographySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_accepts_bangla_digits_and_international_format(): void
    {
        $this->seed(GeographySeeder::class);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'করিম',
            'phone' => '+৮৮০১৭১২৩৪৫৬৭৮',
            'pin' => '১২৩৪৫',
            'district_id' => District::first()->id,
            'user_type' => 'gardener',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.phone', '01712345678')
            ->assertJsonPath('user.role', 'farmer')
            ->assertJsonPath('user.user_type', 'gardener')
            ->assertJsonStructure(['token']);
    }

    public function test_register_rejects_invalid_and_duplicate_phone(): void
    {
        User::factory()->create(['phone' => '01712345678']);

        $this->postJson('/api/v1/auth/register', ['name' => 'A B', 'phone' => '01712345678', 'pin' => '1234'])
            ->assertUnprocessable()->assertJsonValidationErrors('phone');

        $this->postJson('/api/v1/auth/register', ['name' => 'A B', 'phone' => '12345', 'pin' => '1234'])
            ->assertUnprocessable()->assertJsonValidationErrors('phone');

        $this->postJson('/api/v1/auth/register', ['name' => 'A B', 'phone' => '01812345678', 'pin' => '12'])
            ->assertUnprocessable()->assertJsonValidationErrors('pin');
    }

    public function test_register_cannot_escalate_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Sneaky', 'phone' => '01812345678', 'pin' => '1234', 'role' => 'super_admin', 'is_active' => false,
        ])->assertCreated()->assertJsonPath('user.role', 'farmer');

        $this->assertTrue(User::where('phone', '01812345678')->first()->is_active);
    }

    public function test_login_and_logout(): void
    {
        User::factory()->create(['phone' => '01712345678']); // PIN 12345

        $token = $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '12345'])
            ->assertOk()
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.phone', '01712345678');
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_wrong_pin_and_lockout(): void
    {
        User::factory()->create(['phone' => '01712345678']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '00000'])->assertUnprocessable();
        }

        // Even the correct PIN is refused while locked out.
        $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '12345'])->assertStatus(429);
    }

    public function test_disabled_user_cannot_log_in_or_use_token(): void
    {
        $user = User::factory()->create(['phone' => '01712345678']);
        $token = $user->createToken('t')->plainTextToken;
        $user->forceFill(['is_active' => false])->save();

        $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '12345'])->assertUnprocessable();
        $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_change_pin_requires_current_pin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->patchJson('/api/v1/me', ['pin' => '4321', 'current_pin' => '00000'])->assertUnprocessable();
        $this->patchJson('/api/v1/me', ['pin' => '4321', 'current_pin' => '12345', 'name' => 'New Name'])
            ->assertOk()->assertJsonPath('data.name', 'New Name');
    }

    public function test_errors_are_in_bangla_when_the_app_asks_for_it(): void
    {
        User::factory()->create(['phone' => '01712345678']);

        $this->postJson('/api/v1/auth/register', ['name' => 'ক', 'phone' => '01712345678', 'pin' => '12'], ['Accept-Language' => 'bn'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.phone.0', 'এই মোবাইল নম্বরে আগেই অ্যাকাউন্ট খোলা হয়েছে। অনুগ্রহ করে লগইন করুন।')
            ->assertJsonPath('errors.pin.0', 'পিন ৪ থেকে ৬ সংখ্যার হতে হবে।')
            ->assertJsonPath('errors.name.0', 'নাম কমপক্ষে 2 অক্ষরের হতে হবে।');

        $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '99999'], ['Accept-Language' => 'en'])
            ->assertJsonPath('errors.phone.0', 'Mobile number or PIN is incorrect.');
    }
}
