<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * `php artisan migrate --seed` loads:
     *  1. Reference data – districts, fertilizers, crop knowledge base, articles,
     *     markets and commodities. Safe for production.
     *  2. Demo data – staff accounts with known passwords, a demo farmer,
     *     30 days of SAMPLE market prices, sample questions and an alert.
     *     For local testing only. In production run:
     *     php artisan db:seed --class=ReferenceDataSeeder
     */
    public function run(): void
    {
        $this->call([
            ReferenceDataSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
