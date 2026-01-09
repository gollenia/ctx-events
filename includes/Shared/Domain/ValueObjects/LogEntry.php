<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

use Contexis\Events\Shared\Infrastructure\Wordpress\User;
use Psr\Log\LogLevel;

final class LogEntry
{
    public function __construct(
        public readonly string $message,
        public readonly LogLevel $level,
        public readonly User $user,
		public readonly \DateTimeImmutable $timestamp,
    ) {
    }
}