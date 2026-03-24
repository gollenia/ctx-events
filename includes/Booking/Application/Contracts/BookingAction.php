<?php

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;

interface BookingAction
{
    public function execute(BookingActionRequest $request): BookingEmailResult;
}
