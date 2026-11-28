<?php

namespace App\Services\Interview;

/**
 * A request the interview cannot accept (wrong turn, finished, expired...).
 * `code` is a short machine key for the browser; the message is for the fellow.
 */
class InterviewException extends \RuntimeException
{
    public function __construct(string $message, public string $reason = 'invalid', public int $status = 409)
    {
        parent::__construct($message);
    }
}
