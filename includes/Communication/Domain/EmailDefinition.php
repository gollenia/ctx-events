<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\ValueObjects\EmailContext;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class EmailDefinition
{
    public function __construct(
        public string $id,
        public EmailTrigger $trigger,
        public EmailTarget $target,
        public bool $enabled,
        public ?EventId $eventId,
        public ?string $gateway,
        public ?string $subject,
        public string $body,
        public ?Email $replyTo = null,
    ) {
    }

    public function matches(EmailContext $context): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if ($this->trigger !== $context->trigger) {
            return false;
        }

        if ($this->target !== $context->target) {
            return false;
        }

        if ($this->eventId !== null && !$this->eventId->equals($context->eventId)) {
            return false;
        }

        if ($this->gateway !== null && $this->gateway !== $context->gateway) {
            return false;
        }

        return true;
    }

    public function priorityFor(EmailContext $context): int
    {
        if (!$this->matches($context)) {
            return -1;
        }

        $priority = $this->eventId === null ? 10 : 100;

        if ($this->gateway !== null && $this->gateway === $context->gateway) {
            $priority += 10;
        }

        return $priority;
    }
}
