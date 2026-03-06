<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\Contracts\Service;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;
use Contexis\Events\Shared\Domain\Contracts\TokenGenerator;

final class IssueBookingToken implements Service
{
	public function __construct(
		private \Contexis\Events\Booking\Domain\BookingTokenStore $tokenStore,
		private TokenGenerator $tokenGenerator,
		private SessionHashResolver $sessionHashResolver,
	) {
	}

	public function perform(EventId $eventId): string
	{
		$tokenId = $this->tokenGenerator->generate();
		$expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('PT1H'));
		$sessionHash = $this->sessionHashResolver->resolve();

		$record = new BookingTokenRecord(
			tokenId: $tokenId,
			eventId: $eventId->toInt(),
			sessionHash: $sessionHash,
			expiresAt: $expiresAt,
		);

		$this->tokenStore->save($record);

		return $tokenId;
	}
}
