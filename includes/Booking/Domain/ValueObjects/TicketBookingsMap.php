<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;

final class TicketBookingsMap implements \JsonSerializable
{
	/** @var array<string, TicketBookings> */
	private array $keyedItems;
	
	public function __construct(
		array $items
	) {
		$this->keyedItems = [];
        foreach ($items as $item) {
            $this->keyedItems[$item->ticketId->toString()] = $item;
        }
	}

	public static function fromArray(array $data): self
	{
		$items = [];
		foreach ($data as $ticketIdString => $statsData) {
			$ticketId = TicketId::from($ticketIdString);
			$items[] = new TicketBookings(
				ticketId: $ticketId,
				pending: $statsData['pending'] ?? 0,
				approved: $statsData['approved'] ?? 0,
				canceled: $statsData['canceled'] ?? 0,
				expired: $statsData['expired'] ?? 0
			);
		}
		return new self($items);
	}

	public static function empty(): self
	{
		return new self([]);
	}

	public function getCountFor(TicketId $ticketId, BookingStatus $status): int
    {
        return $this->getStatsFor($ticketId)->getCountFor($status);
    }

	public function getStatsFor(TicketId $ticketId): TicketBookings
    {
        $idString = $ticketId->toString();

        if (isset($this->keyedItems[$idString])) {
            return $this->keyedItems[$idString];
        }

        return TicketBookings::empty($ticketId);
    }

	public function getTotalBookedCount(): int
	{
		$total = 0;
		foreach ($this->keyedItems as $stats) {
			$total += $stats->getBookedCount();
		}
		return $total;
	}
	
	public function getTotalPendingCount(): int
	{
		$total = 0;
		foreach ($this->keyedItems as $stats) {
			$total += $stats->getCountFor(BookingStatus::PENDING);
		}
		return $total;
	}

	public function getTotalApprovedCount(): int
	{
		$total = 0;
		foreach ($this->keyedItems as $stats) {
			$total += $stats->getCountFor(BookingStatus::APPROVED);
		}
		return $total;
	}

	public function jsonSerialize(): array
	{
		$result = [];
		foreach ($this->keyedItems as $key => $item) {
			$result[$key] = $item->jsonSerialize();
		}
		return $result;
	}
}