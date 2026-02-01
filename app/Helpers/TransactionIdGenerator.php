<?php

namespace App\Helpers;

class TransactionIdGenerator
{
    public static function generate(
        string $projectId,
        string $sessionId
    ): string {
        return "{$projectId}-{$sessionId}-" . now()->timestamp;
    }
}
