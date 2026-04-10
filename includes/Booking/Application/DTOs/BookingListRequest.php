<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Event\Domain\ValueObjects\EventId;

final readonly class BookingListRequest
{
	/** 
	 * @param array<string> $status
	 */
    public function __construct(
        public ?EventId $eventId = null,
        public ?array $status = null,
        public ?string $search = null,
        public ?string $gateway = null,
        public int $page = 1,
        public int $perPage = 25,
        public string $orderBy = 'date',
        public string $order = 'desc',
    ) {
    }
}
