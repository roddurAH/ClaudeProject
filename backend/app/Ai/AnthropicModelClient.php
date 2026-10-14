<?php

namespace App\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientInterface;

/**
 * Claude via the Anthropic API (official PHP SDK). The key stays on the
 * server; the app only talks to our API.
 */
class AnthropicModelClient implements ModelClient
{
    private const FALLBACK_BETA = 'server-side-fallback-2026-07-01';

    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly string $effort,
        private readonly int $maxTokens,
        private readonly int $timeoutSeconds,
        private readonly ?ClientInterface $transporter = null, // tests inject a fake HTTP client
    ) {}

    public function isConfigured(): bool
    {
        return trim((string) $this->apiKey) !== '';
    }

    public function diagnose(array $system, array $messages): AiReply
    {
        if (! $this->isConfigured()) {
            throw new AiUnavailableException(AiUnavailableException::NOT_CONFIGURED);
        }

        $client = new Client(
            apiKey: $this->apiKey,
            requestOptions: array_filter([
                'timeout' => (float) $this->timeoutSeconds,
                'maxRetries' => 1,
                'transporter' => $this->transporter,
            ], fn ($v) => $v !== null),
        );

        // If a safety classifier declines, the API retries on a suitable model
        // by itself (Haiku has no server-side fallback).
        $fallback = ! str_starts_with($this->model, 'claude-haiku');

        try {
            $message = $client->beta->messages->create(
                maxTokens: $this->maxTokens,
                messages: $messages,
                model: $this->model,
                cacheControl: ['type' => 'ephemeral'], // also caches earlier turns of a conversation
                system: $system,
                outputConfig: [
                    'effort' => $this->effort,
                    'format' => ['type' => 'json_schema', 'schema' => DiagnosisSchema::schema()],
                ],
                fallbacks: $fallback ? 'default' : null,
                betas: $fallback ? [self::FALLBACK_BETA] : null,
            );
        } catch (AuthenticationException|PermissionDeniedException $e) {
            Log::error('AI doctor: the Anthropic API key was rejected', ['error' => $e->getMessage()]);
            throw new AiUnavailableException(AiUnavailableException::NOT_CONFIGURED, $e);
        } catch (RateLimitException|APIConnectionException $e) {
            Log::warning('AI doctor: provider busy or unreachable', ['error' => $e->getMessage()]);
            throw new AiUnavailableException(AiUnavailableException::BUSY, $e);
        } catch (APIException $e) {
            Log::error('AI doctor request failed', ['error' => $e->getMessage()]);
            throw new AiUnavailableException(AiUnavailableException::BUSY, $e);
        }

        $usage = $message->usage;
        $reply = fn (?array $diagnosis) => new AiReply(
            diagnosis: $diagnosis,
            model: $message->model,
            stopReason: $message->stopReason,
            inputTokens: $usage->inputTokens,
            outputTokens: $usage->outputTokens,
            cacheReadTokens: (int) $usage->cacheReadInputTokens,
            cacheWriteTokens: (int) $usage->cacheCreationInputTokens,
        );

        if ($message->stopReason === 'refusal') {
            return $reply(null);
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $data = json_decode($block->text, true);
                if (is_array($data)) {
                    return $reply($data);
                }
            }
        }

        Log::error('AI doctor: no usable answer', ['stop_reason' => $message->stopReason]);
        throw new AiUnavailableException(AiUnavailableException::BUSY);
    }
}
