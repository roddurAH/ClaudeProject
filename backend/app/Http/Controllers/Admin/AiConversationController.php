<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Experts review what the AI doctor told farmers: thumbs-down answers and
 * cases sent on to an expert first. Also shows the estimated AI spend.
 */
class AiConversationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'all')->toString();

        $conversations = AiConversation::query()
            ->with(['user', 'crop', 'district', 'messages'])
            ->when($filter === 'down', fn ($q) => $q->where('rating', 'down'))
            ->when($filter === 'escalated', fn ($q) => $q->whereNotNull('escalated_question_id'))
            ->when($filter === 'expert', fn ($q) => $q->whereHas('messages', fn ($m) => $m->where('result->needs_expert', true)))
            ->when($request->filled('crop_id'), fn ($q) => $q->where('crop_id', $request->integer('crop_id')))
            ->latest('updated_at')
            ->paginate(30)
            ->withQueryString();

        $usage = [
            'today' => AiMessage::where('role', 'assistant')->where('created_at', '>=', today())->count(),
            'cost_today' => AiMessage::costSince(today()),
            'cost_month' => AiMessage::costSince(now()->startOfMonth()),
            'down' => AiConversation::where('rating', 'down')->count(),
        ];

        return view('admin.ai.index', [
            'conversations' => $conversations,
            'filter' => $filter,
            'usage' => $usage,
            'crops' => Crop::orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function show(AiConversation $conversation): View
    {
        return view('admin.ai.show', ['conversation' => $conversation->load(['user', 'crop', 'district', 'messages', 'escalatedQuestion'])]);
    }
}
