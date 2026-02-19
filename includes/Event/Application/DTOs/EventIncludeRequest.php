<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Shared\Application\Contracts\DTO;

final readonly class EventIncludeRequest implements DTO
{
    public function __construct(
        public bool $tickets = false,
        public bool $location = false,
		public bool $author = false,
        public bool $image = false,
        public bool $person = false,
        public bool $bookings = false,
        public bool $categories = false,
        public bool $tags = false
    ) {
    }

    public static function fromArray(array $data): EventIncludeRequest
    {
        return new EventIncludeRequest(
            tickets: in_array('tickets', $data, true),
            location: in_array('location', $data, true),
            author: in_array('author', $data, true),
            person: in_array('person', $data, true),	
            image: in_array('image', $data, true),
            bookings: in_array('bookings', $data, true),
			categories: in_array('categories', $data, true),
			tags: in_array('tags', $data, true)
        );
    }

    public static function createForAll(): EventIncludeRequest
    {
        return new EventIncludeRequest(
            tickets: true,
            location: true,
            image: true,
            person: true,
            bookings: true,
			categories: true,
			tags: true
        );
    }
}
