<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/** Google Play requires in-app and web account deletion plus a public privacy policy. */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** A farmer with a photo question and an answered question. */
    private function farmerWithData(): User
    {
        $user = User::factory()->create(['phone' => '01712345678']); // PIN 12345
        Storage::disk('public')->put('questions/leaf.jpg', 'jpeg');
        Question::factory()->for($user)->create(['image_path' => 'questions/leaf.jpg']);
        Question::factory()->for($user)->answered()->create();

        return $user;
    }

    private function assertEverythingDeleted(User $user): void
    {
        $this->assertModelMissing($user);
        $this->assertSame(0, Question::count());
        $this->assertSame(0, Answer::count());
        $this->assertSame(0, PersonalAccessToken::where('tokenable_id', $user->id)->count());
        Storage::disk('public')->assertMissing('questions/leaf.jpg');
        $this->assertSame(1, User::where('role', Role::Expert)->count(), 'the expert who answered is kept');
    }

    public function test_app_user_deletes_account_with_pin(): void
    {
        $user = $this->farmerWithData();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me', ['pin' => '99999'])
            ->assertUnprocessable()->assertJsonValidationErrors('pin');
        $this->assertModelExists($user);

        $this->withToken($token)->deleteJson('/api/v1/me', ['pin' => '১২৩৪৫'], ['Accept-Language' => 'bn'])
            ->assertOk()->assertJsonPath('message', 'আপনার অ্যাকাউন্ট ও সব তথ্য মুছে ফেলা হয়েছে।');

        $this->assertEverythingDeleted($user);
    }

    public function test_staff_cannot_delete_their_account_from_the_app(): void
    {
        $expert = User::factory()->staff(Role::Expert)->create(['phone' => '01812345678']);

        $this->actingAs($expert, 'sanctum')->deleteJson('/api/v1/me', ['pin' => '12345'])
            ->assertUnprocessable()->assertJsonValidationErrors('pin');
        $this->assertModelExists($expert);
    }

    public function test_web_form_deletes_account_without_the_app(): void
    {
        $user = $this->farmerWithData();

        $this->get('/account/delete')->assertOk()->assertSee('অ্যাকাউন্ট মুছে ফেলুন');
        $this->get('/account/delete?lang=en')->assertOk()->assertSee('Delete my account');

        $this->post('/account/delete', ['phone' => '01712345678', 'pin' => '12345'])
            ->assertSessionHasErrors('confirm');
        $this->post('/account/delete', ['phone' => '01712345678', 'pin' => '11111', 'confirm' => '1'])
            ->assertSessionHasErrors('phone');
        $this->assertModelExists($user);

        $this->post('/account/delete', ['phone' => '+8801712345678', 'pin' => '12345', 'confirm' => '1'])
            ->assertRedirect('/account/delete')->assertSessionHas('deleted');

        $this->assertEverythingDeleted($user);
    }

    public function test_wrong_pins_are_counted_across_web_form_and_app_login(): void
    {
        $user = User::factory()->create(['phone' => '01712345678']);

        foreach (range(1, 5) as $_) {
            $this->post('/account/delete', ['phone' => '01712345678', 'pin' => '00000', 'confirm' => '1']);
        }

        $this->postJson('/api/v1/auth/login', ['phone' => '01712345678', 'pin' => '12345'])->assertTooManyRequests();
        $this->post('/account/delete', ['phone' => '01712345678', 'pin' => '12345', 'confirm' => '1'])
            ->assertSessionHasErrors('phone');
        $this->assertModelExists($user);
    }

    public function test_privacy_policy_is_public_and_links_to_deletion(): void
    {
        config(['krishi.support_email' => 'help@example.org']);

        $this->get('/privacy')->assertOk()
            ->assertSee('গোপনীয়তা নীতি')
            ->assertSee(route('account.delete'))
            ->assertSee('help@example.org');
        $this->get('/privacy?lang=en')->assertOk()->assertSee('Privacy policy');
    }

    public function test_admin_can_delete_app_users_but_not_staff(): void
    {
        $admin = User::factory()->staff(Role::Admin)->create();
        $dataEntry = User::factory()->staff(Role::DataEntry)->create();
        $expert = User::factory()->staff(Role::Expert)->create();
        $farmer = $this->farmerWithData();

        $this->actingAs($dataEntry)->delete("/admin/users/{$farmer->id}")->assertForbidden();
        $this->actingAs($admin)->delete("/admin/users/{$expert->id}")->assertForbidden();
        $this->actingAs($admin)->get("/admin/users/{$farmer->id}/edit")->assertOk()->assertSee('Delete account and all data');

        $this->actingAs($admin)->delete("/admin/users/{$farmer->id}")->assertRedirect('/admin/users');
        $this->assertModelMissing($farmer);
        $this->assertModelExists($expert);
    }
}
