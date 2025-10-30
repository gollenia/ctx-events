<?php

namespace Contexis\Events\Domain\Contracts;

use Contexis\Events\Domain\Models\Booking;
use Contexis\Events\Domain\Collections\BookingCollection;
use Contexis\Events\Domain\Models\EventSpaces;

interface BookingRepository
{
    public function find_by_id(int $id): ?Booking;
    public function find_by_criteria(BookingCriteria $criteria): BookingCollection;
    public function sum_event_spaces(int $eventId): EventSpaces;
    public function sum_spaces(int $eventId, array $statuses = []): int;
    public function save(Booking $booking): void;
    
}