<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\BookingTokenStore;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;

final class BookingTokenValidator
{
	public function __construct(
		private BookingTokenStore $tokenStore,
		private SessionHashResolver $sessionHashResolver
	) {
	}

	public function perform(EventId $eventId, string $token): void
    {
        $record = $this->tokenStore->find($token);

        if ($record === null) {
            throw new \DomainException('Invalid or expired booking token.');
        }

        if ($record->eventId !== $eventId->toInt()) {
            throw new \DomainException('Booking token does not match event.');
        }

        $sessionHash = $this->sessionHashResolver->resolve();
        if ($record->sessionHash !== $sessionHash) {
            throw new \DomainException('Session mismatch. Please reload the page and try again.');
        }

		$this->tokenStore->delete($token);
    }
}