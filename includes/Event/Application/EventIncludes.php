<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\TicketScope;
use Contexis\Events\Shared\Application\Contracts\Includes;

final class EventIncludes implements Includes
{
    public function __construct(
        private readonly TicketScope $tickets = TicketScope::NONE,
        private readonly bool $location = false,
        private readonly bool $image = false,
        private readonly bool $person = false,
        private readonly bool $bookingSpaces = false,
        private readonly bool $categories = false,
        private readonly bool $tags = false
    ) {
    }

    public static function fromArray(array $data): EventIncludes
    {
        return new EventIncludes(
            tickets: in_array('tickets', $data, true)
                ? TicketScope::ALL
                : TicketScope::NONE,
            location: in_array('location', $data, true),
            person: in_array('person', $data, true),
            image: in_array('image', $data, true),
            bookingSpaces: in_array('bookingSpaces', $data, true),
			categories: in_array('categories', $data, true),
			tags: in_array('tags', $data, true)
        );
    }

    public static function createForAll(): EventIncludes
    {
        return new EventIncludes(
            tickets: TicketScope::ALL,
            location: true,
            image: true,
            person: true,
            bookingSpaces: true,
			categories: true,
			tags: true
        );
    }

    public function hasTickets(): bool
    {
        return $this->tickets !== TicketScope::NONE;
    }

    public function allTickets(): bool
    {
        return $this->tickets === TicketScope::ALL;
    }

    public function hasLocation(): bool
    {
        return $this->location;
    }

    public function hasImage(): bool
    {
        return $this->image;
    }

    public function hasPerson(): bool
    {
        return $this->person;
    }

    public function hasBookingSpaces(): bool
    {
        return $this->bookingSpaces;
    }

	public function hasCategories(): bool
	{
		return $this->categories;
	}

	public function hasTags(): bool
	{
		return $this->tags;
	}
}
