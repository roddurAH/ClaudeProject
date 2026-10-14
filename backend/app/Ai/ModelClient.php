<?php

namespace App\Ai;

/**
 * Sends one AI doctor request. Implementations: AnthropicModelClient (Claude)
 * and DemoModelClient (canned answers without an API key). Tests bind fakes.
 */
interface ModelClient
{
    /**
     * @param  list<array<string, mixed>>  $system  system prompt blocks
     * @param  list<array<string, mixed>>  $messages  alternating user/assistant turns
     *
     * @throws AiUnavailableException
     */
    public function diagnose(array $system, array $messages): AiReply;

    /** False when the driver cannot work (e.g. no API key). */
    public function isConfigured(): bool;
}
