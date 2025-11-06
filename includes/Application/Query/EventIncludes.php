<?php

namespace Contexis\Events\Application\Query;

final class EventIncludes
{
    public function __construct(
        public readonly bool $tickets = false,
        public readonly bool $location = false,
        public readonly bool $image = false,
        public readonly bool $bookingSpaces = false,
        public readonly bool $forms = false
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tickets: in_array('tickets', $data, true),
            location: in_array('location', $data, true),
            image: in_array('image', $data, true),
            bookingSpaces: in_array('bookingSpaces', $data, true),
            forms: in_array('forms', $data, true)
        );
    }
}
