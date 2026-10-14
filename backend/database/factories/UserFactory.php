<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '017'.fake()->unique()->numerify('########'),
            'email' => null,
            'password' => static::$password ??= Hash::make('12345'),
            'role' => Role::Farmer,
            'user_type' => 'farmer',
            'preferred_language' => 'bn',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /** Staff member with the given role and password "Secret@123". */
    public function staff(Role $role = Role::Admin): static
    {
        return $this->state(fn () => [
            'role' => $role,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Secret@123'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
