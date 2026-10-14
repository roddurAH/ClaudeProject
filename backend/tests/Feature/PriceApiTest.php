<?php

namespace Tests\Feature;

use App\Models\Commodity;
use App\Models\District;
use App\Models\Market;
use App\Models\MarketPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function price(Market $market, Commodity $commodity, string $date, float $min, float $max, string $type = 'wholesale'): void
    {
        MarketPrice::create([
            'market_id' => $market->id, 'commodity_id' => $commodity->id, 'price_date' => $date,
            'price_type' => $type, 'min_price' => $min, 'max_price' => $max,
        ]);
    }

    public function test_board_shows_latest_price_and_trend(): void
    {
        $market = Market::where('name_en', 'Kawran Bazar')->first();
        $potato = Commodity::where('name_en', 'Potato')->first();
        $onion = Commodity::where('name_en', 'Onion (local)')->first();

        $this->price($market, $potato, '2026-10-12', 20, 22);
        $this->price($market, $potato, '2026-10-13', 24, 26); // +18% → up
        $this->price($market, $onion, '2026-10-12', 60, 62);
        $this->price($market, $onion, '2026-10-13', 50, 52);  // down

        $items = collect($this->getJson("/api/v1/prices?market_id={$market->id}")
            ->assertOk()
            ->assertJsonPath('date', '2026-10-13')
            ->json('items'))->keyBy('commodity.name_en');

        $this->assertSame('up', $items['Potato']['trend']);
        $this->assertEquals(25, $items['Potato']['avg']);
        $this->assertSame('down', $items['Onion (local)']['trend']);
    }

    public function test_nearest_market_is_chosen_from_district(): void
    {
        $potato = Commodity::where('name_en', 'Potato')->first();
        foreach (Market::all() as $m) {
            $this->price($m, $potato, '2026-10-13', 20, 22);
        }

        $naogaon = District::where('name_en', 'Naogaon')->first();
        $this->getJson("/api/v1/prices?district_id={$naogaon->id}")
            ->assertOk()
            ->assertJsonPath('market.name_en', 'Mahasthan Haat'); // Bogura is closest with data
    }

    public function test_history_and_validation(): void
    {
        $market = Market::first();
        $potato = Commodity::where('name_en', 'Potato')->first();
        $this->price($market, $potato, now()->subDays(2)->toDateString(), 20, 22);
        $this->price($market, $potato, now()->subDay()->toDateString(), 22, 24);

        $this->getJson("/api/v1/prices/history?commodity_id={$potato->id}&market_id={$market->id}&days=7")
            ->assertOk()->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/prices/history')->assertUnprocessable();
        $this->getJson('/api/v1/prices?type=black-market')->assertUnprocessable();
    }

    public function test_empty_board_when_no_data(): void
    {
        $this->getJson('/api/v1/prices')->assertOk()->assertJsonPath('items', []);
    }

    public function test_fertilizer_prices(): void
    {
        $this->getJson('/api/v1/fertilizer-prices')->assertOk()
            ->assertJsonFragment(['key' => 'urea', 'official_price_per_kg' => 27]);
    }
}
