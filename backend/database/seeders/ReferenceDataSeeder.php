<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GeographySeeder::class,
            FertilizerSeeder::class,
            KnowledgeBaseSeeder::class,
            MarketSeeder::class,
        ]);
    }
}
