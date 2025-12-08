<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

interface BookingRepository
{
    public function find(string $id);
}
