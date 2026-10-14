<?php

namespace Tests\Feature;

use App\Models\District;
use Database\Seeders\GeographySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    private function openMeteoPayload(float $rain = 30): array
    {
        $dates = collect(range(0, 6))->map(fn ($i) => now()->addDays($i)->toDateString())->all();

        return [
            'current' => ['temperature_2m' => 29.4, 'relative_humidity_2m' => 80, 'weather_code' => 61, 'wind_speed_10m' => 9.1],
            'daily' => [
                'time' => $dates,
                'weather_code' => array_fill(0, 7, 61),
                'temperature_2m_max' => array_fill(0, 7, 31.2),
                'temperature_2m_min' => array_fill(0, 7, 24.5),
                'precipitation_sum' => [2, $rain, 5, 0, 0, 0, 0],
                'precipitation_probability_max' => [70, 90, 40, 10, 10, 10, 10],
                'wind_speed_10m_max' => array_fill(0, 7, 12),
            ],
        ];
    }

    public function test_forecast_with_advisories_is_cached(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->openMeteoPayload())]);
        $dhaka = District::where('name_en', 'Dhaka')->first();

        $this->getJson("/api/v1/weather?district_id={$dhaka->id}")
            ->assertOk()
            ->assertJsonPath('district.name_en', 'Dhaka')
            ->assertJsonPath('current.temperature', 29.4)
            ->assertJsonCount(7, 'daily')
            ->assertJsonPath('advisories.0.key', 'heavy_rain')
            ->assertJsonPath('stale', false);

        $this->getJson("/api/v1/weather?district_id={$dhaka->id}")->assertOk();
        Http::assertSentCount(1); // second call served from cache
    }

    public function test_stale_forecast_is_served_when_provider_fails(): void
    {
        $dhaka = District::where('name_en', 'Dhaka')->first();
        // First call succeeds, every later call (incl. retries) fails.
        Http::fake(['api.open-meteo.com/*' => Http::sequence()
            ->push($this->openMeteoPayload())
            ->whenEmpty(Http::response('down', 500))]);
        $this->getJson("/api/v1/weather?district_id={$dhaka->id}")->assertOk()->assertJsonPath('stale', false);

        Cache::forget("weather:district:{$dhaka->id}"); // fresh cache expired

        $this->getJson("/api/v1/weather?district_id={$dhaka->id}")->assertOk()->assertJsonPath('stale', true);
    }

    public function test_503_when_provider_fails_and_nothing_cached(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response('down', 500)]);
        $district = District::first();

        $this->getJson("/api/v1/weather?district_id={$district->id}")->assertStatus(503);
    }

    public function test_district_is_required(): void
    {
        $this->getJson('/api/v1/weather')->assertUnprocessable();
    }
}
