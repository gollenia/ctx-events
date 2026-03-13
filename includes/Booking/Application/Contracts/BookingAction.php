<?php

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;

interface BookingAction
{
    public function execute(BookingActionRequest $request): void;
}