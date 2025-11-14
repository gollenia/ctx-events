<?php

namespace Contexis\Events\Booking\Domain;

interface BookingRepository
{
    public function find(string $id);
}
