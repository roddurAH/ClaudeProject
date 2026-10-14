<?php

namespace Tests\Feature;

use App\Models\Crop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_content_bundle_contains_full_knowledge_base(): void
    {
        $response = $this->getJson('/api/v1/content')->assertOk();

        $this->assertCount(Crop::count(), $response->json('crops'));
        $this->assertNotEmpty($response->json('fertilizers'));
        $this->assertNotEmpty($response->json('articles'));

        $rice = collect($response->json('crops'))->firstWhere('slug', 'boro-rice');
        $this->assertNotEmpty($rice['stages']);
        $this->assertNotEmpty($rice['problems']);
        $this->assertNotEmpty($rice['fertilizer_doses']);
        $this->assertNotEmpty($rice['cultivation_bn']);
    }

    public function test_etag_returns_304_until_content_changes(): void
    {
        $etag = $this->getJson('/api/v1/content')->headers->get('ETag');
        $this->assertNotEmpty($etag);

        $this->getJson('/api/v1/content', ['If-None-Match' => $etag])->assertStatus(304);

        $this->travel(1)->minutes();
        Crop::first()->update(['summary_en' => 'Changed']);

        $this->getJson('/api/v1/content', ['If-None-Match' => $etag])->assertOk();
    }

    public function test_unpublished_crops_are_hidden(): void
    {
        Crop::where('slug', 'jute')->update(['is_published' => false]);

        $slugs = collect($this->getJson('/api/v1/content')->json('crops'))->pluck('slug');
        $this->assertNotContains('jute', $slugs);
        $this->getJson('/api/v1/crops/jute')->assertNotFound();
    }

    public function test_crop_list_and_garden_filter(): void
    {
        $garden = collect($this->getJson('/api/v1/crops?garden=1')->assertOk()->json('data'));

        $this->assertTrue($garden->every(fn ($c) => $c['is_garden_friendly']));
        $this->assertContains('tomato', $garden->pluck('slug'));
        $this->assertNotContains('boro-rice', $garden->pluck('slug'));
    }

    public function test_crop_detail_and_problem(): void
    {
        $crop = $this->getJson('/api/v1/crops/potato')->assertOk()->json('data');
        $problemId = $crop['problems'][0]['id'];

        $this->getJson("/api/v1/problems/{$problemId}")->assertOk()->assertJsonPath('data.crop_id', $crop['id']);
    }

    public function test_bangla_is_sent_as_raw_utf8_and_gzipped(): void
    {
        $plain = $this->get('/api/v1/districts');
        $this->assertStringContainsString('ঢাকা', $plain->getContent());

        $gzipped = $this->get('/api/v1/content', ['Accept-Encoding' => 'gzip']);
        $gzipped->assertHeader('Content-Encoding', 'gzip');
        $this->assertNotEmpty(json_decode(gzdecode($gzipped->getContent()), true)['crops']);
    }

    public function test_meta_endpoint(): void
    {
        $this->getJson('/api/v1/meta')->assertOk()
            ->assertJsonPath('helpline_number', '16123')
            ->assertJsonStructure(['content_version', 'units' => ['decimal_per_bigha']]);
    }
}
