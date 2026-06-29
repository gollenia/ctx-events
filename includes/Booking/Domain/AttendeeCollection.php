<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Shared\Domain\Abstract\Collection;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final readonly class AttendeeCollection extends Collection
{
    public static function from(Attendee ...$attendees): self
    {
        return new self($attendees);
    }

	/** @return string[] */
	public function getTicketIds(): array
    {
        return array_map(fn(Attendee $a) => $a->ticketId->toString(), $this->items);
    }

	/** @return array<string, int> */
	public function countTicketsById(): array
	{
		$counts = [];
		foreach ($this->items as $attendee) {
            if (!$attendee->isActive()) {
                continue;
            }

			$ticketId = $attendee->ticketId->toString();
			if (!isset($counts[$ticketId])) {
				$counts[$ticketId] = 0;
			}
			$counts[$ticketId]++;
		}
		return $counts;
	}

    public function countActive(): int
    {
        return count(array_filter(
            $this->items,
            static fn (Attendee $attendee): bool => $attendee->isActive(),
        ));
    }

    public function getById(AttendeeId $id): ?Attendee
    {
        foreach ($this->items as $attendee) {
            if ($attendee->id?->equals($id) ?? false) {
                return $attendee;
            }
        }

        return null;
    }

    public function replaceById(AttendeeId $id, Attendee $replacement): self
    {
        $replaced = false;
        $items = array_map(
            static function (Attendee $attendee) use ($id, $replacement, &$replaced): Attendee {
                if ($attendee->id?->equals($id) ?? false) {
                    $replaced = true;
                    return $replacement;
                }

                return $attendee;
            },
            $this->items,
        );

        if (!$replaced) {
            throw new \DomainException('Attendee not found.');
        }

        return self::from(...$items);
    }

    public function totalPrice(?Currency $currency = null): Price
    {
        $resolvedCurrency = $currency ?? $this->items[0]?->ticketPrice->currency ?? Currency::fromCode('EUR');
        $total = Price::from(0, $resolvedCurrency);

        foreach ($this->items as $attendee) {
            $total = $total->add($attendee->ticketPrice);
        }

        return $total;
    }
}
