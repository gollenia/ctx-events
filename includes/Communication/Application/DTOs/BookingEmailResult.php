<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;

final readonly class BookingEmailResult
{
    /** @param list<BookingEmailDeliveryResult> $deliveries */
    public function __construct(
        public array $deliveries = [],
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }

    public function withDelivery(BookingEmailDeliveryResult $delivery): self
    {
        return new self([...$this->deliveries, $delivery]);
    }

    public function hasFailures(): bool
    {
        foreach ($this->deliveries as $delivery) {
            if ($delivery->failed()) {
                return true;
            }
        }

        return false;
    }

    public function hasFailuresForTarget(EmailTarget $target): bool
    {
        foreach ($this->deliveries as $delivery) {
            if ($delivery->target === $target && $delivery->failed()) {
                return true;
            }
        }

        return false;
    }
}
