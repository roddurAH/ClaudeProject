<?php

namespace App\Ai;

use App\Models\Problem;

/**
 * KRISHI_AI_DRIVER=demo: answers without calling any AI, so the app can be
 * tried before an API key is set up. Every answer says it is a demo.
 */
class DemoModelClient implements ModelClient
{
    public function isConfigured(): bool
    {
        return true;
    }

    public function diagnose(array $system, array $messages): AiReply
    {
        $last = end($messages);
        $context = collect($last['content'] ?? [])->firstWhere('type', 'text')['text'] ?? '';
        $english = str_contains($context, 'language: en');
        preg_match('/crop_id: (\d+)/', $context, $m);

        $problem = Problem::published()
            ->when($m[1] ?? null, fn ($q, $cropId) => $q->where('crop_id', $cropId))
            ->orderBy('id')
            ->first();

        $name = $problem ? ($english ? $problem->name_en : $problem->name_bn) : null;
        $prevention = $problem ? ($english ? $problem->prevention_en : $problem->prevention_bn) : null;
        $steps = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $prevention)))), 0, 3);

        return new AiReply(
            diagnosis: [
                'is_plant_photo' => true,
                'answer' => $english
                    ? 'DEMO ANSWER – the real AI is not switched on for this server. '.($name ? "An example diagnosis: {$name}." : '')
                    : 'এটি ডেমো উত্তর – এই সার্ভারে আসল AI চালু করা হয়নি। '.($name ? "উদাহরণ হিসেবে: {$name}।" : ''),
                'likely_problems' => $problem ? [['problem_id' => $problem->id, 'name' => $name, 'confidence' => 'low']] : [],
                'steps' => $steps,
                'safety_note' => '',
                'needs_expert' => true,
                'follow_up_question' => $english ? 'Can you send a closer photo of the affected leaf?' : 'আক্রান্ত পাতার আরও কাছ থেকে একটি ছবি দিতে পারবেন?',
            ],
            model: 'demo',
            stopReason: 'end_turn',
        );
    }
}
