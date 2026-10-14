<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\FertilizerSeeder;
use Database\Seeders\GeographySeeder;
use Database\Seeders\KnowledgeBaseSeeder;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedReferenceData(): void
    {
        $this->seed([GeographySeeder::class, FertilizerSeeder::class, KnowledgeBaseSeeder::class, MarketSeeder::class]);
    }

    protected function staff(Role $role): User
    {
        return User::factory()->staff($role)->create();
    }
}
