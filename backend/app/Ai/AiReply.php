<?php

namespace App\Ai;

/** A model answer plus what it cost. `diagnosis` is null when the model declined. */
final class AiReply
{
    /** @param array<string, mixed>|null $diagnosis */
    public function __construct(
        public readonly ?array $diagnosis,
        public readonly string $model,
        public readonly ?string $stopReason = null,
        public readonly int $inputTokens = 0,
        public readonly int $outputTokens = 0,
        public readonly int $cacheReadTokens = 0,
        public readonly int $cacheWriteTokens = 0,
    ) {}
}
