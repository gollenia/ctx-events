<?php

namespace Contexis\Events\Platform\Config;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Person\Infrastructure\PersonPost;
use Contexis\Events\Payment\Infrastructure\CouponPost;
use Contexis\Events\Form\Infrastructure\BookingFormPost; 
use Contexis\Events\Form\Infrastructure\AttendeeFormPost;

use function DI\get;

return [
    get(EventPost::class),
    get(LocationPost::class),
    get(PersonPost::class),
    get(CouponPost::class),
    get(BookingFormPost::class),
    get(AttendeeFormPost::class),
];