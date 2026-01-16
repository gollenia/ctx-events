<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Event\Presentation\EventController::class),
    get(\Contexis\Events\Location\Presentation\LocationController::class),
    get(\Contexis\Events\Person\Presentation\PersonController::class),
    get(\Contexis\Events\Shared\Presentation\OptionController::class),
    get(\Contexis\Events\Payment\Presentation\GatewayController::class),
    //get(\Contexis\Events\Form\Presentation\FormController::class),
	//get(\Contexis\Events\Booking\Presentation\BookingController::class),
	//get(\Contexis\Events\Booking\Presentation\AttendeeController::class),
];