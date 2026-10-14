<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuestionStatus;
use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Expert inbox: answer farmers' questions and choose which to publish. */
class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', QuestionStatus::Pending->value);

        $questions = Question::query()
            ->with(['user', 'crop', 'district'])
            ->withCount('answers')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('crop_id'), fn ($q) => $q->where('crop_id', $request->integer('crop_id')))
            // Pending: oldest first (fair queue). Others: newest first.
            ->orderBy('created_at', $status === QuestionStatus::Pending->value ? 'asc' : 'desc')
            ->paginate(25)
            ->withQueryString();

        $crops = Crop::orderBy('sort_order')->get(['id', 'name_en']);

        return view('admin.questions.index', compact('questions', 'crops', 'status'));
    }

    public function show(Question $question): View
    {
        $question->load(['user.district', 'crop', 'district', 'answers.expert']);

        $related = Question::query()
            ->where('id', '!=', $question->id)
            ->where('status', QuestionStatus::Answered)
            ->when($question->crop_id, fn ($q) => $q->where('crop_id', $question->crop_id))
            ->with('answers')
            ->latest('answered_at')
            ->limit(5)
            ->get();

        return view('admin.questions.show', compact('question', 'related'));
    }

    public function answer(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $question->answers()->create([
            'body' => $data['body'],
            'user_id' => $request->user()->id,
        ]);

        $question->status = QuestionStatus::Answered;
        $question->answered_at = now();
        $question->is_public = $request->boolean('is_public');
        $question->save();

        return redirect()->route('admin.questions.index')->with('status', 'Answer sent to the farmer.');
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'is_public' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(QuestionStatus::class)],
        ]);

        $question->forceFill($data)->save();

        return back()->with('status', 'Question updated.');
    }
}
