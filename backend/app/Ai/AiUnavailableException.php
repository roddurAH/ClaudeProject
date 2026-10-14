<?php

namespace App\Ai;

use RuntimeException;

/** The AI could not answer right now (busy, not configured, network). Shown to the user, never the raw error. */
class AiUnavailableException extends RuntimeException
{
    public const BUSY = 'busy';

    public const NOT_CONFIGURED = 'not_configured';

    public function __construct(public readonly string $reason, ?\Throwable $previous = null)
    {
        parent::__construct('AI unavailable: '.$reason, 0, $previous);
    }
}
