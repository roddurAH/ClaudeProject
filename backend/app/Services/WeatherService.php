<?php

namespace App\Services;

use App\Exceptions\WeatherUnavailableException;
use App\Models\District;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Fetches a 7-day forecast from Open-Meteo for a district and attaches farm
 * advisories. The server – not each phone – talks to the provider, so:
 *  - the app downloads one small JSON instead of a heavy third-party payload,
 *  - results are cached per district (one upstream call serves everyone),
 *  - the provider can be swapped (e.g. for BMD/BAMIS) without an app update.
 * If the provider is down we return the last good forecast flagged "stale".
 */
class WeatherService
{
    public function __construct(private readonly FarmAdvisoryService $advisor) {}

    public function forecastFor(District $district): array
    {
        $key = "weather:district:{$district->id}";
        $lastGoodKey = "weather:last:{$district->id}";

        if ($cached = Cache::get($key)) {
            return $cached + ['stale' => false];
        }

        try {
            $data = $this->fetch($district);
            Cache::put($key, $data, now()->addMinutes(config('krishi.weather.cache_minutes')));
            Cache::forever($lastGoodKey, $data);

            return $data + ['stale' => false];
        } catch (Throwable $e) {
            Log::warning('Weather provider failed', ['district' => $district->id, 'error' => $e->getMessage()]);

            if ($last = Cache::get($lastGoodKey)) {
                return $last + ['stale' => true];
            }

            throw new WeatherUnavailableException('Weather service is temporarily unavailable.', previous: $e);
        }
    }

    private function fetch(District $district): array
    {
        $response = Http::timeout(config('krishi.weather.timeout_seconds'))
            ->retry(2, 300, throw: false)
            ->get(config('krishi.weather.base_url'), [
                'latitude' => $district->latitude,
                'longitude' => $district->longitude,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max',
                'timezone' => 'Asia/Dhaka',
                'forecast_days' => 7,
            ])
            ->throw();

        $json = $response->json();
        $d = $json['daily'] ?? [];

        $daily = [];
        foreach ($d['time'] ?? [] as $i => $date) {
            $daily[] = [
                'date' => $date,
                'code' => $d['weather_code'][$i] ?? null,
                't_max' => $this->round($d['temperature_2m_max'][$i] ?? null),
                't_min' => $this->round($d['temperature_2m_min'][$i] ?? null),
                'rain_mm' => $this->round($d['precipitation_sum'][$i] ?? null),
                'rain_probability' => $d['precipitation_probability_max'][$i] ?? null,
                'wind_max' => $this->round($d['wind_speed_10m_max'][$i] ?? null),
            ];
        }

        $c = $json['current'] ?? [];
        $current = [
            'temperature' => $this->round($c['temperature_2m'] ?? null),
            'humidity' => $c['relative_humidity_2m'] ?? null,
            'code' => $c['weather_code'] ?? null,
            'wind' => $this->round($c['wind_speed_10m'] ?? null),
        ];

        return [
            'district' => [
                'id' => $district->id,
                'name_bn' => $district->name_bn,
                'name_en' => $district->name_en,
            ],
            'current' => $current,
            'daily' => $daily,
            'advisories' => $this->advisor->advise($daily, $current, now()),
            'source' => 'Open-Meteo',
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    private function round(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 1);
    }
}
