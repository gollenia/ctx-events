<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Shared\Application\Contracts\Includes;

final class EventIncludes implements Includes
{
    public function __construct(
        public readonly bool $tickets = false,
        public readonly bool $location = false,
        public readonly bool $image = false,
        public readonly bool $person = false,
        public readonly bool $bookingSpaces = false,
        public readonly bool $forms = false
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tickets: in_array('tickets', $data, true),
            location: in_array('location', $data, true),
            person: in_array('person', $data, true),
            image: in_array('image', $data, true),
            bookingSpaces: in_array('bookingSpaces', $data, true),
            forms: in_array('forms', $data, true)
        );
    }
}
