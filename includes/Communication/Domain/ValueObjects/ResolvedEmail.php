<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class ResolvedEmail
{
    public function __construct(
        public Email $to,
        public string $subject,
        public string $body,
        public ?Email $replyTo = null,
    ) {
    }
}