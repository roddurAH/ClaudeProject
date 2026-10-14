<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\AiUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AiAskRequest;
use App\Http\Resources\AiConversationResource;
use App\Http\Resources\AiMessageResource;
use App\Http\Resources\QuestionResource;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiDoctorService;
use App\Support\BanglaText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * AI crop doctor. Signed-in users only (daily limit per person, see
 * config krishi.ai.daily_limit). The model call takes 10-30 seconds.
 */
class AiController extends Controller
{
    public function __construct(private readonly AiDoctorService $doctor) {}

    public function ask(AiAskRequest $request): JsonResponse
    {
        if (! $this->doctor->isAvailable()) {
            return response()->json(['message' => __('The AI doctor is not available right now. Please ask an expert instead.')], 503);
        }

        $user = $request->user();
        $limit = (int) config('krishi.ai.daily_limit');
        $usedToday = AiMessage::where('role', 'user')
            ->whereHas('conversation', fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', today())
            ->count();
        if ($usedToday >= $limit) {
            $shown = app()->getLocale() === 'bn' ? BanglaText::toBanglaDigits((string) $limit) : (string) $limit;

            return response()->json(['message' => __('You have used today\'s :limit AI questions. Try again tomorrow, or ask an expert.', ['limit' => $shown])], 429);
        }

        $conversation = null;
        if ($request->filled('conversation_id')) {
            $conversation = AiConversation::where('user_id', $user->id)->findOrFail($request->integer('conversation_id'));
        }

        set_time_limit((int) config('krishi.ai.timeout_seconds') + 30);

        try {
            $answer = $this->doctor->ask(
                $user,
                $conversation,
                $request->file('image'),
                $request->validated('text'),
                $request->validated('crop_id'),
                app()->getLocale(),
            );
        } catch (AiUnavailableException $e) {
            return response()->json(['message' => $e->reason === AiUnavailableException::BUSY
                ? __('The AI doctor is busy. Please try again in a minute.')
                : __('The AI doctor is not available right now. Please ask an expert instead.')], 503);
        }

        return response()->json([
            'conversation_id' => $answer->ai_conversation_id,
            'message' => new AiMessageResource($answer),
            'remaining_today' => max(0, $limit - $usedToday - 1),
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->latest('updated_at')
            ->paginate(20);

        return AiConversationResource::collection($conversations);
    }

    public function show(Request $request, AiConversation $conversation): AiConversationResource
    {
        $this->authorizeOwner($request, $conversation);

        return new AiConversationResource($conversation->load('messages'));
    }

    public function rate(Request $request, AiConversation $conversation): AiConversationResource
    {
        $this->authorizeOwner($request, $conversation);
        $data = $request->validate([
            'rating' => ['required', 'in:up,down'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);
        $conversation->forceFill($data)->save();

        return new AiConversationResource($conversation);
    }

    public function escalate(Request $request, AiConversation $conversation): JsonResponse
    {
        $this->authorizeOwner($request, $conversation);
        $question = $this->doctor->escalate($conversation->load('messages'));

        return (new QuestionResource($question->load(['crop', 'district', 'answers.expert'])))
            ->response()
            ->setStatusCode(201);
    }

    private function authorizeOwner(Request $request, AiConversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);
    }
}
