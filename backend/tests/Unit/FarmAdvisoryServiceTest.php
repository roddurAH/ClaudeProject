<?php

namespace Tests\Unit;

use App\Services\FarmAdvisoryService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class FarmAdvisoryServiceTest extends TestCase
{
    private function day(array $overrides = []): array
    {
        return array_merge([
            'date' => '2026-10-14', 'code' => 1, 't_max' => 30.0, 't_min' => 22.0,
            'rain_mm' => 0.0, 'rain_probability' => 10, 'wind_max' => 10.0,
        ], $overrides);
    }

    private function keys(array $advice): array
    {
        return array_column($advice, 'key');
    }

    public function test_heavy_rain_warns_against_spraying(): void
    {
        $daily = [$this->day(), $this->day(['rain_mm' => 35]), $this->day()];
        $advice = (new FarmAdvisoryService)->advise($daily, ['humidity' => 70, 'code' => 1], Carbon::parse('2026-07-10'));

        $this->assertContains('heavy_rain', $this->keys($advice));
        $this->assertNotContains('spray_window', $this->keys($advice));
        $this->assertSame('warning', $advice[0]['level']);
    }

    public function test_very_heavy_rain_is_danger(): void
    {
        $advice = (new FarmAdvisoryService)->advise([$this->day(['rain_mm' => 80])], ['humidity' => 90], Carbon::parse('2026-07-10'));

        $this->assertSame('danger', $advice[0]['level']);
    }

    public function test_cold_wave_and_late_blight_in_winter(): void
    {
        $daily = array_fill(0, 3, $this->day(['t_max' => 18, 't_min' => 9.5]));
        $advice = (new FarmAdvisoryService)->advise($daily, ['humidity' => 95, 'code' => 45], Carbon::parse('2026-01-05'));

        $this->assertContains('cold', $this->keys($advice));
        $this->assertContains('late_blight', $this->keys($advice));
    }

    public function test_heat_and_dry_spell(): void
    {
        $daily = array_fill(0, 7, $this->day(['t_max' => 38]));
        $advice = (new FarmAdvisoryService)->advise($daily, ['humidity' => 40], Carbon::parse('2026-04-15'));

        $this->assertContains('heat', $this->keys($advice));
        $this->assertContains('dry_spell', $this->keys($advice));
    }

    public function test_storm_wind(): void
    {
        $advice = (new FarmAdvisoryService)->advise([$this->day(['wind_max' => 55])], [], Carbon::parse('2026-05-01'));

        $this->assertContains('strong_wind', $this->keys($advice));
    }

    public function test_every_item_is_bilingual(): void
    {
        $daily = array_fill(0, 7, $this->day(['t_max' => 38, 'rain_mm' => 25, 'wind_max' => 50]));
        foreach ((new FarmAdvisoryService)->advise($daily, [], Carbon::parse('2026-06-01')) as $item) {
            foreach (['title_bn', 'title_en', 'body_bn', 'body_en'] as $field) {
                $this->assertNotEmpty($item[$field], "{$item['key']} is missing {$field}");
            }
        }
    }
}
