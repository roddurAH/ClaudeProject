<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $this->defineGates();
        $this->defineRateLimits();
    }

    /**
     * Role-based permissions. Routes use them via the `can:` middleware and
     * Blade uses them via @can to hide menu items.
     *
     * | Ability          | super_admin | admin | expert | data_entry |
     * |------------------|-------------|-------|--------|------------|
     * | manage-content   | ✓           | ✓     | ✓      |            |
     * | answer-questions | ✓           | ✓     | ✓      |            |
     * | manage-prices    | ✓           | ✓     |        | ✓          |
     * | manage-alerts    | ✓           | ✓     |        |            |
     * | manage-users     | ✓           | ✓     |        |            |
     * | manage-admins    | ✓           |       |        |            |
     */
    private function defineGates(): void
    {
        $abilities = [
            'manage-content' => [Role::SuperAdmin, Role::Admin, Role::Expert],
            'answer-questions' => [Role::SuperAdmin, Role::Admin, Role::Expert],
            'manage-prices' => [Role::SuperAdmin, Role::Admin, Role::DataEntry],
            'manage-alerts' => [Role::SuperAdmin, Role::Admin],
            'manage-users' => [Role::SuperAdmin, Role::Admin],
            'manage-admins' => [Role::SuperAdmin],
        ];

        foreach ($abilities as $ability => $roles) {
            Gate::define($ability, fn (User $user) => $user->is_active && $user->hasRole(...$roles));
        }
    }

    private function defineRateLimits(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        // Registration / login: per IP. Per-phone lockout is in AuthController.
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Each phone may ask 15 questions per hour – plenty for genuine use.
        RateLimiter::for('questions', fn (Request $request) => Limit::perHour(15)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('weather', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.$request->input('login')));
    }
}
