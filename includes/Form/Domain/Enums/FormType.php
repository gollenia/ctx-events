<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

enum FormType: string
{
    case BOOKING = 'booking';
    case ATTENDEE = 'attendee';
}
