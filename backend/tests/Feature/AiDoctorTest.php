<?php

namespace Tests\Feature;

use App\Ai\AnthropicModelClient;
use App\Ai\ModelClient;
use App\Enums\Role;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\District;
use App\Models\Problem;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Psr\Http\Message\ResponseInterface;
use Tests\Support\FakeHttpClient;
use Tests\TestCase;

/**
 * AI crop doctor. The real AnthropicModelClient runs against a fake HTTP
 * client, so the request we send to Claude is checked too – no API calls.
 */
class AiDoctorTest extends TestCase
{
    use RefreshDatabase;

    private Problem $tomatoProblem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Storage::fake('public');
        config(['krishi.ai.driver' => 'anthropic', 'krishi.ai.api_key' => 'sk-test', 'krishi.ai.daily_limit' => 10]);
        $this->tomatoProblem = Problem::whereHas('crop', fn ($q) => $q->where('slug', 'tomato'))->orderBy('id')->firstOrFail();
    }

    /** @param array<string, mixed>|ResponseInterface|null ...$replies diagnosis arrays (null = refusal) or raw responses */
    private function fakeClaude(array|ResponseInterface|null ...$replies): FakeHttpClient
    {
        $fake = new FakeHttpClient(array_map(fn ($r) => $r instanceof ResponseInterface ? $r : FakeHttpClient::json([
            'id' => 'msg_'.uniqid(),
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-opus-5-5',
            'content' => $r === null ? [] : [
                ['type' => 'thinking', 'thinking' => '', 'signature' => 'sig'],
                ['type' => 'text', 'text' => json_encode($r, JSON_UNESCAPED_UNICODE)],
            ],
            'stop_reason' => $r === null ? 'refusal' : 'end_turn',
            'stop_sequence' => null,
            'usage' => ['input_tokens' => 1800, 'output_tokens' => 650, 'cache_read_input_tokens' => 5200, 'cache_creation_input_tokens' => 0],
        ]), $replies));

        $this->app->instance(ModelClient::class, new AnthropicModelClient('sk-test', 'claude-opus-5-5', 'medium', 16000, 30, $fake));

        return $fake;
    }

    private function diagnosis(array $overrides = []): array
    {
        return array_merge([
            'is_plant_photo' => true,
            'answer' => 'এটি সম্ভবত টমেটোর পাতা কোঁকড়ানো ভাইরাস।',
            'likely_problems' => [
                ['problem_id' => $this->tomatoProblem->id, 'name' => $this->tomatoProblem->name_bn, 'confidence' => 'high'],
                ['problem_id' => 999999, 'name' => 'অজানা', 'confidence' => 'low'],
            ],
            'steps' => ['আক্রান্ত গাছ তুলে ফেলুন।', 'হলুদ আঠালো ফাঁদ দিন।'],
            'safety_note' => '',
            'needs_expert' => false,
            'follow_up_question' => '',
        ], $overrides);
    }

    private function farmer(): User
    {
        return User::factory()->create(['district_id' => District::where('name_en', 'Bogura')->value('id')]);
    }

    private function ask(User $user, array $data = []): TestResponse
    {
        return $this->actingAs($user, 'sanctum')->post('/api/v1/ai/ask', $data + [
            'image' => UploadedFile::fake()->image('leaf.jpg', 800, 600),
            'text' => 'টমেটো গাছের পাতা কুঁকড়ে যাচ্ছে',
            'crop_id' => $this->tomatoProblem->crop_id,
        ], ['Accept' => 'application/json', 'Accept-Language' => 'bn']);
    }

    public function test_guest_cannot_use_the_ai_doctor(): void
    {
        $this->postJson('/api/v1/ai/ask', ['text' => 'help'])->assertUnauthorized();
    }

    public function test_photo_question_gets_an_answer_grounded_in_the_knowledge_base(): void
    {
        $fake = $this->fakeClaude($this->diagnosis());

        $response = $this->ask($this->farmer())->assertOk()
            ->assertJsonPath('message.role', 'assistant')
            ->assertJsonPath('message.result.answer', 'এটি সম্ভবত টমেটোর পাতা কোঁকড়ানো ভাইরাস।')
            ->assertJsonPath('message.result.likely_problems.0.problem_id', $this->tomatoProblem->id)
            ->assertJsonPath('message.result.likely_problems.1.problem_id', null) // unknown id dropped
            ->assertJsonPath('message.result.safety_note', null)
            ->assertJsonPath('remaining_today', 9);

        // What Claude received: cached system prompt with our catalogue, the photo, the context line.
        $body = $fake->lastBody();
        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame('medium', $body['output_config']['effort']);
        $this->assertSame('json_schema', $body['output_config']['format']['type']);
        $this->assertSame('default', $body['fallbacks']);
        $this->assertSame(['type' => 'ephemeral'], $body['system'][1]['cache_control']);
        $this->assertStringContainsString('<knowledge_base>', $body['system'][1]['text']);
        $this->assertStringContainsString($this->tomatoProblem->name_bn, $body['system'][1]['text']);
        $content = $body['messages'][0]['content'];
        $this->assertSame('image', $content[0]['type']);
        $this->assertSame('image/jpeg', $content[0]['source']['media_type']);
        $this->assertStringContainsString('language: bn', $content[1]['text']);
        $this->assertStringContainsString('crop_id: '.$this->tomatoProblem->crop_id, $content[1]['text']);
        $this->assertStringContainsString('district: Bogura', $content[1]['text']);
        $this->assertSame('server-side-fallback-2026-07-01', $fake->requests[0]->getHeaderLine('anthropic-beta'));

        $answer = AiMessage::where('role', 'assistant')->firstOrFail();
        $this->assertSame(1800, $answer->input_tokens);
        $this->assertSame(5200, $answer->cache_read_tokens);
        $this->assertGreaterThan(0, $answer->estimatedCost());
        Storage::disk('public')->assertExists(AiMessage::where('role', 'user')->value('image_path'));
        $this->assertSame($response->json('conversation_id'), $answer->ai_conversation_id);
    }

    public function test_follow_up_continues_the_conversation_with_the_same_cached_prefix(): void
    {
        $fake = $this->fakeClaude($this->diagnosis(['follow_up_question' => 'পাতার নিচে সাদা মাছি আছে?']), $this->diagnosis());
        $user = $this->farmer();

        $first = $this->ask($user)->assertOk();
        $this->ask($user, ['image' => null, 'text' => 'হ্যাঁ, অনেক সাদা মাছি', 'conversation_id' => $first->json('conversation_id')])
            ->assertOk()
            ->assertJsonPath('conversation_id', $first->json('conversation_id'));

        $firstBody = json_decode((string) $fake->requests[0]->getBody(), true);
        $secondBody = $fake->lastBody();
        $this->assertSame($firstBody['system'], $secondBody['system'], 'system prompt must stay byte-identical for caching');
        $this->assertSame(['user', 'assistant', 'user'], array_column($secondBody['messages'], 'role'));
        $this->assertSame($firstBody['messages'][0], $secondBody['messages'][0], 'earlier turns are resent unchanged');
        $this->assertStringContainsString('সাদা মাছি আছে', $secondBody['messages'][1]['content']);
    }

    public function test_daily_limit(): void
    {
        config(['krishi.ai.daily_limit' => 1]);
        $fake = $this->fakeClaude($this->diagnosis());
        $user = $this->farmer();

        $this->ask($user)->assertOk();
        $this->ask($user)->assertStatus(429)->assertJsonPath('message', 'আজকের ১টি AI প্রশ্ন শেষ হয়েছে। কাল আবার চেষ্টা করুন, অথবা বিশেষজ্ঞকে প্রশ্ন করুন।');
        $this->assertCount(1, $fake->requests);
    }

    public function test_busy_provider_gives_a_friendly_error_and_leaves_nothing_behind(): void
    {
        $overloaded = fn () => FakeHttpClient::json(['type' => 'error', 'error' => ['type' => 'overloaded_error', 'message' => 'Overloaded']], 529);
        $this->fakeClaude($overloaded(), $overloaded()); // the SDK retries once

        $this->ask($this->farmer())->assertStatus(503)->assertJsonPath('message', 'AI ডাক্তার এখন ব্যস্ত। এক মিনিট পরে আবার চেষ্টা করুন।');

        $this->assertSame(0, AiConversation::count());
        $this->assertSame(0, AiMessage::count());
        $this->assertSame([], Storage::disk('public')->allFiles('ai'));
    }

    public function test_a_declined_answer_offers_the_expert(): void
    {
        $this->fakeClaude(null);

        $this->ask($this->farmer())->assertOk()->assertJsonPath('message.result', null);
        $this->assertSame('refusal', AiMessage::where('role', 'assistant')->value('stop_reason'));
    }

    public function test_without_api_key_the_ai_is_switched_off(): void
    {
        config(['krishi.ai.api_key' => '']);

        $this->ask($this->farmer())->assertStatus(503);
        $this->getJson('/api/v1/meta')->assertJsonPath('ai.enabled', false);
    }

    public function test_demo_driver_answers_without_a_key(): void
    {
        config(['krishi.ai.driver' => 'demo', 'krishi.ai.api_key' => '']);
        $this->app->forgetInstance(ModelClient::class);

        $this->getJson('/api/v1/meta')->assertJsonPath('ai.enabled', true);
        $this->ask($this->farmer())->assertOk()
            ->assertJsonPath('message.result.likely_problems.0.problem_id', $this->tomatoProblem->id)
            ->assertJsonPath('message.result.needs_expert', true);
        $this->assertStringStartsWith('এটি ডেমো উত্তর', AiMessage::where('role', 'assistant')->value('text'));
    }

    public function test_validation(): void
    {
        $user = $this->farmer();
        $this->fakeClaude();

        $this->ask($user, ['image' => null, 'text' => null])->assertUnprocessable()->assertJsonValidationErrors(['image', 'text']);
        $this->ask($user, ['image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php')])->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->ask($user, ['conversation_id' => 12345])->assertNotFound();
    }

    public function test_send_to_expert_creates_a_question_with_its_own_photo_copy(): void
    {
        $this->fakeClaude($this->diagnosis());
        $user = $this->farmer();
        $conversationId = $this->ask($user)->json('conversation_id');

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/ai/conversations/{$conversationId}/escalate")
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $question = Question::findOrFail($response->json('data.id'));
        $this->assertStringContainsString('টমেটো গাছের পাতা কুঁকড়ে যাচ্ছে', $question->body);
        $this->assertStringContainsString('AI-র প্রাথমিক উত্তর', $question->body);
        $this->assertStringContainsString('1. আক্রান্ত গাছ তুলে ফেলুন।', $question->body);
        $this->assertNotSame(AiMessage::where('role', 'user')->value('image_path'), $question->image_path);
        Storage::disk('public')->assertExists($question->image_path);

        // Sending again does not duplicate; other users cannot see it.
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/ai/conversations/{$conversationId}/escalate")
            ->assertCreated()->assertJsonPath('data.id', $question->id);
        $this->actingAs($this->farmer(), 'sanctum')->postJson("/api/v1/ai/conversations/{$conversationId}/escalate")->assertNotFound();
        $this->assertSame(1, Question::count());
    }

    public function test_rating_and_history(): void
    {
        $this->fakeClaude($this->diagnosis());
        $user = $this->farmer();
        $conversationId = $this->ask($user)->json('conversation_id');

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/ai/conversations/{$conversationId}/rate", ['rating' => 'down', 'feedback' => 'ভুল রোগ'])
            ->assertOk()->assertJsonPath('data.rating', 'down');
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/ai/conversations/{$conversationId}/rate", ['rating' => 'meh'])->assertUnprocessable();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/ai/conversations')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonCount(2, 'data.0.messages');
        $this->actingAs($this->farmer(), 'sanctum')->getJson("/api/v1/ai/conversations/{$conversationId}")->assertNotFound();
    }

    public function test_deleting_the_account_removes_ai_photos(): void
    {
        $this->fakeClaude($this->diagnosis());
        $user = $this->farmer();
        $this->ask($user)->assertOk();
        $photo = AiMessage::where('role', 'user')->value('image_path');

        $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/me', ['pin' => '12345'])->assertOk();

        $this->assertSame(0, AiConversation::count());
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_experts_review_ai_conversations_in_the_admin_panel(): void
    {
        $this->fakeClaude($this->diagnosis(['needs_expert' => true]));
        $this->ask($this->farmer())->assertOk();
        $conversation = AiConversation::firstOrFail();

        $expert = $this->staff(Role::Expert);
        $this->actingAs($expert)->get('/admin/ai-conversations')->assertOk()->assertSee('AI suggested an expert')->assertSee('টমেটো গাছের পাতা');
        $this->actingAs($expert)->get('/admin/ai-conversations?filter=expert')->assertOk()->assertSee('টমেটো গাছের পাতা');
        $this->actingAs($expert)->get("/admin/ai-conversations/{$conversation->id}")->assertOk()
            ->assertSee('এটি সম্ভবত টমেটোর পাতা কোঁকড়ানো ভাইরাস।')
            ->assertSee('KB #'.$this->tomatoProblem->id);
        $this->actingAs($expert)->get('/admin')->assertOk()->assertSee('AI answers today');

        $this->actingAs($this->staff(Role::DataEntry))->get('/admin/ai-conversations')->assertForbidden();
    }
}
