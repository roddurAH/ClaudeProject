<?php

namespace Tests\Feature;

use App\Enums\QuestionStatus;
use App\Enums\Role;
use App\Models\Alert;
use App\Models\Commodity;
use App\Models\Crop;
use App\Models\District;
use App\Models\Fertilizer;
use App\Models\Market;
use App\Models\MarketPrice;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Storage::fake('public');
    }

    public function test_expert_answers_a_question_and_publishes_it(): void
    {
        $expert = $this->staff(Role::Expert);
        $question = Question::factory()->create();

        $this->actingAs($expert)->get("/admin/questions/{$question->id}")->assertOk()->assertSee($question->body);

        $this->actingAs($expert)->post("/admin/questions/{$question->id}/answers", [
            'body' => 'ফেরোমন ফাঁদ ব্যবহার করুন।',
            'is_public' => 1,
        ])->assertRedirect('/admin/questions');

        $question->refresh();
        $this->assertSame(QuestionStatus::Answered, $question->status);
        $this->assertTrue($question->is_public);
        $this->assertNotNull($question->answered_at);

        // The farmer sees the answer in the app.
        $this->actingAs($question->user, 'sanctum')->getJson("/api/v1/questions/{$question->id}")
            ->assertJsonPath('data.answers.0.body', 'ফেরোমন ফাঁদ ব্যবহার করুন।')
            ->assertJsonPath('data.answers.0.expert_name', $expert->name);
    }

    public function test_create_crop_with_image_stage_dose_and_problem(): void
    {
        $expert = $this->staff(Role::Expert);

        $this->actingAs($expert)->post('/admin/crops', [
            'name_bn' => 'পালংশাক', 'name_en' => 'Spinach', 'category' => 'vegetable',
            'sowing_months' => [10, 11], 'is_garden_friendly' => 1, 'is_published' => 1,
            'image' => UploadedFile::fake()->image('spinach.jpg', 800, 600),
        ])->assertRedirect();

        $crop = Crop::where('slug', 'spinach')->firstOrFail();
        $this->assertSame([10, 11], array_map('intval', $crop->sowing_months));
        Storage::disk('public')->assertExists($crop->image_path);

        $this->actingAs($expert)->post("/admin/crops/{$crop->id}/stages", [
            'day_offset' => 0, 'stage_type' => 'sowing', 'title_bn' => 'বপন', 'title_en' => 'Sowing',
        ])->assertRedirect();
        $this->actingAs($expert)->post("/admin/crops/{$crop->id}/doses", [
            'fertilizer_id' => Fertilizer::where('key', 'urea')->value('id'), 'kg_per_hectare' => 120,
        ])->assertRedirect();
        $this->actingAs($expert)->post('/admin/problems', [
            'crop_id' => $crop->id, 'type' => 'pest', 'affected_part' => 'leaf', 'severity' => 'low',
            'name_bn' => 'জাব পোকা', 'name_en' => 'Aphid', 'symptoms_bn' => 'পাতায় পোকা', 'symptoms_en' => 'Insects on leaves',
            'is_published' => 1,
        ])->assertRedirect();

        $content = collect($this->getJson('/api/v1/content')->json('crops'))->firstWhere('slug', 'spinach');
        $this->assertCount(1, $content['stages']);
        $this->assertCount(1, $content['fertilizer_doses']);
        $this->assertCount(1, $content['problems']);
        $this->assertStringContainsString('/media/', $content['image_url']);

        // Uploaded image is served through the media route.
        $this->get(parse_url($content['image_url'], PHP_URL_PATH))->assertOk();
    }

    public function test_crop_validation(): void
    {
        $this->actingAs($this->staff(Role::Admin))
            ->post('/admin/crops', ['name_bn' => '', 'name_en' => 'X', 'category' => 'spaceship'])
            ->assertSessionHasErrors(['name_bn', 'category']);
    }

    public function test_daily_price_entry_saves_filled_rows_only(): void
    {
        $officer = $this->staff(Role::DataEntry);
        $market = Market::first();
        [$potato, $onion, $garlic] = Commodity::orderBy('id')->skip(5)->take(3)->get()->all();

        $this->actingAs($officer)->post('/admin/prices/entry', [
            'market_id' => $market->id,
            'price_date' => '2026-10-14',
            'price_type' => 'wholesale',
            'source' => 'Field survey',
            'prices' => [
                $potato->id => ['min' => 20, 'max' => 24],
                $onion->id => ['min' => '', 'max' => ''],   // skipped
                $garlic->id => ['min' => 120, 'max' => ''], // single value → min = max
            ],
        ])->assertRedirect();

        $this->assertSame(2, MarketPrice::count());
        $garlicPrice = MarketPrice::where('commodity_id', $garlic->id)->first();
        $this->assertEquals(120, $garlicPrice->max_price);
        $this->assertSame($officer->id, $garlicPrice->entered_by);

        // Re-entering the same day updates instead of duplicating.
        $this->actingAs($officer)->post('/admin/prices/entry', [
            'market_id' => $market->id, 'price_date' => '2026-10-14', 'price_type' => 'wholesale',
            'prices' => [$potato->id => ['min' => 21, 'max' => 25]],
        ]);
        $this->assertSame(2, MarketPrice::count());
    }

    public function test_csv_import_reports_bad_rows(): void
    {
        $officer = $this->staff(Role::DataEntry);
        $market = Market::where('name_en', 'Kawran Bazar')->first();
        $potato = Commodity::where('name_en', 'Potato')->first();
        $csv = "date,market,commodity,type,min_price,max_price,source\n"
            ."2026-10-14,Kawran Bazar,Potato,wholesale,20,24,DAM\n"
            ."2026-10-14,{$market->id},{$potato->id},retail,28,30,DAM\n"
            ."2026-10-14,Nowhere Market,Potato,wholesale,20,24,DAM\n"
            ."2026-10-14,Kawran Bazar,Potato,wholesale,30,20,DAM\n";

        $this->actingAs($officer)->post('/admin/prices/import', [
            'file' => UploadedFile::fake()->createWithContent('prices.csv', $csv),
        ])->assertRedirect('/admin/prices/import')->assertSessionHasErrors();

        $this->assertSame(2, MarketPrice::count());
    }

    public function test_targeted_alert_reaches_only_selected_districts(): void
    {
        $admin = $this->staff(Role::Admin);
        $sunamganj = District::where('name_en', 'Sunamganj')->first();
        $rajshahi = District::where('name_en', 'Rajshahi')->first();

        $this->actingAs($admin)->post('/admin/alerts', [
            'severity' => 'danger',
            'title_bn' => 'হাওরে আগাম বন্যার সতর্কতা', 'title_en' => 'Early flash flood warning in haor',
            'body_bn' => '৮০% পাকা ধান দ্রুত কেটে ফেলুন।', 'body_en' => 'Harvest rice that is 80% mature now.',
            'is_national' => 0, 'district_ids' => [$sunamganj->id],
            'starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
        ])->assertRedirect('/admin/alerts');

        $this->assertSame(1, Alert::count());
        $this->getJson("/api/v1/alerts?district_id={$sunamganj->id}")->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/alerts?district_id={$rajshahi->id}")->assertJsonCount(0, 'data');
    }

    public function test_alert_needs_districts_when_not_national(): void
    {
        $this->actingAs($this->staff(Role::Admin))->post('/admin/alerts', [
            'severity' => 'info', 'title_bn' => 'ক', 'title_en' => 'A', 'body_bn' => 'খ', 'body_en' => 'B',
            'is_national' => 0, 'starts_at' => now()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('district_ids');
    }

    public function test_media_route_blocks_path_traversal(): void
    {
        $this->get('/media/../.env')->assertNotFound();
    }
}
