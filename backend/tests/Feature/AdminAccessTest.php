<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Article;
use App\Models\Commodity;
use App\Models\Crop;
use App\Models\Fertilizer;
use App\Models\Market;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Sign in');
    }

    public function test_staff_can_log_in_with_email_or_phone(): void
    {
        $admin = User::factory()->staff(Role::Admin)->create(['phone' => '01700000009']);

        $this->post('/admin/login', ['login' => $admin->email, 'password' => 'Secret@123'])->assertRedirect('/admin');
        $this->post('/admin/logout');

        $this->post('/admin/login', ['login' => '+8801700000009', 'password' => 'Secret@123'])->assertRedirect('/admin');
    }

    public function test_app_users_cannot_log_in_to_admin(): void
    {
        $farmer = User::factory()->create(['phone' => '01712345678']);

        $this->post('/admin/login', ['login' => '01712345678', 'password' => '12345'])->assertSessionHasErrors('login');
        $this->actingAs($farmer)->get('/admin')->assertForbidden();
    }

    public function test_disabled_staff_are_signed_out(): void
    {
        $expert = User::factory()->staff(Role::Expert)->inactive()->create();

        $this->actingAs($expert)->get('/admin')->assertRedirect('/admin/login');
    }

    /**
     * @return array<string, array{Role, array<string, int>}>
     */
    public static function rolePages(): array
    {
        return [
            'super admin' => [Role::SuperAdmin, ['/admin' => 200, '/admin/crops' => 200, '/admin/prices/entry' => 200, '/admin/questions' => 200, '/admin/alerts' => 200, '/admin/users' => 200]],
            'admin' => [Role::Admin, ['/admin' => 200, '/admin/crops' => 200, '/admin/prices/entry' => 200, '/admin/questions' => 200, '/admin/alerts' => 200, '/admin/users' => 200]],
            'expert' => [Role::Expert, ['/admin' => 200, '/admin/crops' => 200, '/admin/problems' => 200, '/admin/questions' => 200, '/admin/prices/entry' => 403, '/admin/alerts' => 403, '/admin/users' => 403]],
            'data entry' => [Role::DataEntry, ['/admin' => 200, '/admin/prices/entry' => 200, '/admin/markets' => 200, '/admin/fertilizers' => 200, '/admin/crops' => 403, '/admin/questions' => 403, '/admin/users' => 403]],
        ];
    }

    #[DataProvider('rolePages')]
    public function test_role_based_access(Role $role, array $pages): void
    {
        $user = $this->staff($role);

        foreach ($pages as $url => $status) {
            $this->actingAs($user)->get($url)->assertStatus($status);
        }
    }

    public function test_every_admin_page_renders_for_super_admin(): void
    {
        $admin = $this->staff(Role::SuperAdmin);
        $crop = Crop::first();
        $problem = Problem::first();
        $article = Article::first()->id;
        $market = Market::first()->id;
        $commodity = Commodity::first()->id;
        $fertilizer = Fertilizer::first()->id;

        $pages = [
            '/admin', '/admin/crops', '/admin/crops/create', "/admin/crops/{$crop->id}/edit",
            "/admin/stages/{$crop->stages()->first()->id}/edit",
            '/admin/problems', '/admin/problems/create', "/admin/problems/{$problem->id}/edit",
            '/admin/articles', '/admin/articles/create', "/admin/articles/{$article}/edit",
            '/admin/questions', '/admin/prices', '/admin/prices/entry', '/admin/prices/import',
            '/admin/markets', '/admin/markets/create', "/admin/markets/{$market}/edit",
            '/admin/commodities', '/admin/commodities/create', "/admin/commodities/{$commodity}/edit",
            '/admin/fertilizers', "/admin/fertilizers/{$fertilizer}/edit",
            '/admin/alerts', '/admin/alerts/create', '/admin/users', '/admin/users/create', "/admin/users/{$admin->id}/edit",
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_admin_cannot_create_super_admin_or_edit_admins(): void
    {
        $admin = $this->staff(Role::Admin);
        $other = $this->staff(Role::Admin);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Boss', 'phone' => '01711112222', 'role' => 'super_admin', 'password' => 'Password123', 'is_active' => 1,
        ])->assertForbidden();

        $this->actingAs($admin)->get("/admin/users/{$other->id}/edit")->assertForbidden();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Expert', 'phone' => '01711112222', 'role' => 'expert', 'password' => 'Password123', 'is_active' => 1,
        ])->assertRedirect('/admin/users');
        $this->assertSame(Role::Expert, User::where('phone', '01711112222')->first()->role);
    }

    public function test_staff_password_policy_and_app_user_pin(): void
    {
        $admin = $this->staff(Role::SuperAdmin);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Weak', 'phone' => '01711113333', 'role' => 'expert', 'password' => '12345', 'is_active' => 1,
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Farmer', 'phone' => '01711113333', 'role' => 'farmer', 'password' => '12345', 'is_active' => 1,
        ])->assertRedirect('/admin/users');
    }

    public function test_nobody_can_demote_themselves(): void
    {
        $admin = $this->staff(Role::SuperAdmin);

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'phone' => $admin->phone, 'role' => 'farmer',
        ])->assertSessionHasErrors('role');
    }

    public function test_disabling_a_user_revokes_app_tokens(): void
    {
        $admin = $this->staff(Role::Admin);
        $farmer = User::factory()->create();
        $farmer->createToken('phone');

        $this->actingAs($admin)->put("/admin/users/{$farmer->id}", [
            'name' => $farmer->name, 'phone' => $farmer->phone, 'role' => 'farmer', 'is_active' => 0,
        ])->assertRedirect('/admin/users');

        $this->assertSame(0, $farmer->tokens()->count());
    }
}
