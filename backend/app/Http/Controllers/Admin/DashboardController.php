<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuestionStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AiMessage;
use App\Models\Alert;
use App\Models\Crop;
use App\Models\MarketPrice;
use App\Models\Problem;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'app_users' => User::where('role', Role::Farmer)->count(),
            'new_users_week' => User::where('role', Role::Farmer)->where('created_at', '>=', now()->subWeek())->count(),
            'pending_questions' => Question::where('status', QuestionStatus::Pending)->count(),
            'answered_questions' => Question::where('status', QuestionStatus::Answered)->count(),
            'crops' => Crop::count(),
            'problems' => Problem::count(),
            'prices_today' => MarketPrice::whereDate('price_date', today())->count(),
            'last_price_date' => MarketPrice::max('price_date'),
            'active_alerts' => Alert::active()->count(),
            'ai_today' => AiMessage::where('role', 'assistant')->where('created_at', '>=', today())->count(),
            'ai_cost_today' => AiMessage::costSince(today()),
        ];

        $oldestPending = Question::with(['user', 'crop', 'district'])
            ->where('status', QuestionStatus::Pending)
            ->oldest()
            ->limit(8)
            ->get();

        $usersByType = User::where('role', Role::Farmer)
            ->select('user_type', DB::raw('COUNT(*) as total'))
            ->groupBy('user_type')
            ->pluck('total', 'user_type');

        $topCrops = Question::query()
            ->join('crops', 'crops.id', '=', 'questions.crop_id')
            ->select('crops.name_en', 'crops.name_bn', DB::raw('COUNT(*) as total'))
            ->groupBy('crops.id', 'crops.name_en', 'crops.name_bn')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'oldestPending', 'usersByType', 'topCrops'));
    }
}
