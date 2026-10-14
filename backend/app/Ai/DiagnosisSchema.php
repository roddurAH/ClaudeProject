<?php

namespace App\Ai;

/**
 * The shape of every AI doctor answer (Claude structured output). The app
 * shows it as a card and reads `answer` aloud. Optional values use "" / 0
 * instead of null to keep the schema simple.
 */
final class DiagnosisSchema
{
    /** @return array<string, mixed> */
    public static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'is_plant_photo' => [
                    'type' => 'boolean',
                    'description' => 'False when the photo or question is not about plants, crops, gardening, soil, fertilizer or plant pests.',
                ],
                'answer' => [
                    'type' => 'string',
                    'description' => 'Short spoken answer, 2-4 sentences, plain text without lists: what it most likely is and the most important thing to do now.',
                ],
                'likely_problems' => [
                    'type' => 'array',
                    'description' => 'Up to 3 likely problems, most likely first. Empty when the plant looks healthy or it is not a plant.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'problem_id' => ['type' => 'integer', 'description' => 'Knowledge-base id when one of its problems matches, otherwise 0.'],
                            'name' => ['type' => 'string', 'description' => 'Problem name (the knowledge-base name when it matches).'],
                            'confidence' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                        ],
                        'required' => ['problem_id', 'name', 'confidence'],
                        'additionalProperties' => false,
                    ],
                ],
                'steps' => [
                    'type' => 'array',
                    'description' => '2-5 practical steps in order, cheapest and safest first; one short sentence each, no numbering. Empty when not a plant.',
                    'items' => ['type' => 'string'],
                ],
                'safety_note' => [
                    'type' => 'string',
                    'description' => 'Safety warning when a chemical is mentioned (protective clothing, label dose, days to wait before harvest); otherwise "".',
                ],
                'needs_expert' => [
                    'type' => 'boolean',
                    'description' => 'True when a human agriculture expert should check this.',
                ],
                'follow_up_question' => [
                    'type' => 'string',
                    'description' => 'One specific question or photo request that would make the diagnosis surer; otherwise "".',
                ],
            ],
            'required' => ['is_plant_photo', 'answer', 'likely_problems', 'steps', 'safety_note', 'needs_expert', 'follow_up_question'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Cleans a decoded answer: known keys only, at most 3 problems / 6 steps,
     * problem ids that are not in our published knowledge base become null.
     *
     * @param  array<string, mixed>  $raw
     * @param  list<int>  $knownProblemIds
     * @return array<string, mixed>
     */
    public static function normalize(array $raw, array $knownProblemIds): array
    {
        $problems = [];
        foreach (array_slice((array) ($raw['likely_problems'] ?? []), 0, 3) as $p) {
            if (! is_array($p) || trim((string) ($p['name'] ?? '')) === '') {
                continue;
            }
            $id = (int) ($p['problem_id'] ?? 0);
            $problems[] = [
                'problem_id' => in_array($id, $knownProblemIds, true) ? $id : null,
                'name' => trim((string) $p['name']),
                'confidence' => in_array($p['confidence'] ?? '', ['high', 'medium', 'low'], true) ? $p['confidence'] : 'low',
            ];
        }

        $text = fn (string $key) => ($v = trim((string) ($raw[$key] ?? ''))) === '' ? null : $v;

        return [
            'is_plant_photo' => (bool) ($raw['is_plant_photo'] ?? true),
            'answer' => trim((string) ($raw['answer'] ?? '')),
            'likely_problems' => $problems,
            'steps' => array_values(array_filter(array_map(
                fn ($s) => trim((string) $s),
                array_slice((array) ($raw['steps'] ?? []), 0, 6),
            ))),
            'safety_note' => $text('safety_note'),
            'needs_expert' => (bool) ($raw['needs_expert'] ?? false),
            'follow_up_question' => $text('follow_up_question'),
        ];
    }
}
