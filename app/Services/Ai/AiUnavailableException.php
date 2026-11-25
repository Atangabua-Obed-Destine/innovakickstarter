<?php

namespace App\Services\Ai;

/**
 * No AI provider could answer (none configured, or all failed). The message
 * lists what each provider said.
 */
class AiUnavailableException extends \RuntimeException
{
}
