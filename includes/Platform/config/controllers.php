<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Event\Presentation\EventController::class),
    get(\Contexis\Events\Event\Presentation\EventExportController::class),
    get(\Contexis\Events\Location\Presentation\LocationController::class),
    get(\Contexis\Events\Person\Presentation\PersonController::class),
    get(\Contexis\Events\Shared\Presentation\OptionController::class),
    get(\Contexis\Events\Payment\Presentation\GatewayController::class),
    get(\Contexis\Events\Payment\Presentation\CouponController::class),
    get(\Contexis\Events\Payment\Presentation\PaymentQrController::class),
    get(\Contexis\Events\Payment\Presentation\PaymentReconciliationController::class),
    get(\Contexis\Events\Payment\Presentation\PaymentWebhookController::class),
	get(\Contexis\Events\Form\Presentation\FormController::class),
	get(\Contexis\Events\Booking\Presentation\BookingController::class),
	get(\Contexis\Events\Booking\Presentation\BookingNoteController::class),
	get(\Contexis\Events\Booking\Presentation\BookingActionController::class),
    get(\Contexis\Events\Communication\Presentation\EmailController::class),
];
