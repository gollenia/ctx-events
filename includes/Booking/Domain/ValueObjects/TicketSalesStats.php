<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;

final class TicketSalesStats
{
	private array $keyedItems;
	/**
	 * @param TicketSalesCount[] $items
	 */
	public function __construct(
		array $items
	) {
		$this->keyedItems = [];
        foreach ($items as $item) {
            $this->keyedItems[(string)$item->ticketId] = $item;
        }
	}

	public function getCountFor(TicketId $ticketId, BookingStatus $status): int
    {
        return $this->getStatsFor($ticketId)->getCountFor($status);
    }

	public function getStatsFor(TicketId $ticketId): TicketSalesCount
    {
        $idString = (string)$ticketId;

        if (isset($this->keyedItems[$idString])) {
            return $this->keyedItems[$idString];
        }

        return TicketSalesCount::empty($ticketId);
    }
}