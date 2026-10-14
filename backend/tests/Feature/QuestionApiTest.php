<?php

namespace Tests\Feature;

use App\Enums\QuestionStatus;
use App\Models\Crop;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Storage::fake('public');
    }

    public function test_guest_cannot_ask(): void
    {
        $this->postJson('/api/v1/questions', ['body' => 'Help please'])->assertUnauthorized();
    }

    public function test_farmer_asks_with_photo_and_it_is_resized(): void
    {
        $user = User::factory()->create();
        $crop = Crop::where('slug', 'tomato')->first();

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/questions', [
            'body' => 'টমেটো গাছের পাতা কুঁকড়ে যাচ্ছে',
            'crop_id' => $crop->id,
            'image' => UploadedFile::fake()->image('leaf.png', 3000, 2000),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.crop.name_en', 'Tomato');

        $question = Question::first();
        Storage::disk('public')->assertExists($question->image_path);
        $this->assertStringEndsWith('.jpg', $question->image_path);

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($question->image_path));
        $this->assertLessThanOrEqual(1280, max($width, $height));
    }

    public function test_rejects_non_images_and_short_text(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->post('/api/v1/questions', [
            'body' => 'Hi',
            'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body', 'image']);
    }

    public function test_users_see_only_their_own_questions(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $bobsQuestion = Question::factory()->for($bob)->create();

        $this->actingAs($alice, 'sanctum')->getJson('/api/v1/questions')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($alice, 'sanctum')->getJson("/api/v1/questions/{$bobsQuestion->id}")->assertNotFound();
        $this->actingAs($bob, 'sanctum')->getJson("/api/v1/questions/{$bobsQuestion->id}")->assertOk()->assertJsonPath('data.is_mine', true);
    }

    public function test_community_shows_only_public_answered_questions_without_identity(): void
    {
        $public = Question::factory()->answered()->create(['is_public' => true, 'body' => 'Public answered']);
        Question::factory()->answered()->create(['is_public' => false]);
        Question::factory()->create(['is_public' => true]); // pending

        $response = $this->getJson('/api/v1/community/questions')->assertOk()->assertJsonCount(1, 'data');

        $this->assertSame($public->id, $response->json('data.0.id'));
        $this->assertStringNotContainsString($public->user->phone, $response->getContent());
        $this->assertStringNotContainsString($public->user->name, $response->getContent());
    }

    public function test_question_rate_limit(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            $this->actingAs($user, 'sanctum')->postJson('/api/v1/questions', ['body' => "Question number {$i}"])->assertCreated();
        }
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/questions', ['body' => 'One too many'])->assertStatus(429);
        $this->assertSame(15, Question::where('status', QuestionStatus::Pending)->count());
    }
}
