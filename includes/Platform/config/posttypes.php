<?php

namespace Contexis\Events\Platform\Config;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Person\Infrastructure\PersonPost;
use Contexis\Events\Payment\Infrastructure\CouponPost;
//use Contexis\Events\Form\Infrastructure\BookingFormPost; // TODO: implement
//use Contexis\Events\Form\Infrastructure\AttendeeFormPost; // TODO: implement

use function DI\get;

return [
    get(EventPost::class),
    get(LocationPost::class),
    get(PersonPost::class),
    get(CouponPost::class),
];