<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\QuestionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * "Ask an expert": a farmer sends a question (optionally with a photo); an
 * expert answers from the admin panel; the farmer sees the answer in the app.
 */
class QuestionController extends Controller
{
    private const RELATIONS = ['crop', 'district', 'answers.expert'];

    /** The signed-in user's own questions. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $questions = $request->user()->questions()
            ->with(self::RELATIONS)
            ->latest()
            ->paginate(20);

        return QuestionResource::collection($questions);
    }

    public function store(StoreQuestionRequest $request, ImageService $images)
    {
        $data = $request->validated();
        $user = $request->user();

        $question = new Question([
            'body' => $data['body'],
            'crop_id' => $data['crop_id'] ?? null,
            'district_id' => $user->district_id,
        ]);
        $question->user()->associate($user);

        if ($request->hasFile('image')) {
            $question->image_path = $images->store($request->file('image'), 'questions');
        }

        $question->save();

        return (new QuestionResource($question->load(self::RELATIONS)))
            ->response()
            ->setStatusCode(201);
    }

    /** Owner can always see it; others only if published to the community. */
    public function show(Request $request, Question $question): QuestionResource
    {
        $isOwner = $request->user()?->id === $question->user_id;
        abort_unless($isOwner || ($question->is_public && $question->status === QuestionStatus::Answered), 404);

        return new QuestionResource($question->load(self::RELATIONS));
    }

    /** Public, answered questions – a growing, searchable knowledge base. */
    public function community(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'crop_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $questions = Question::query()
            ->where('is_public', true)
            ->where('status', QuestionStatus::Answered)
            ->when($data['crop_id'] ?? null, fn ($q, $cropId) => $q->where('crop_id', $cropId))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->where('body', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->with(self::RELATIONS)
            ->latest('answered_at')
            ->paginate(20);

        return QuestionResource::collection($questions);
    }
}
