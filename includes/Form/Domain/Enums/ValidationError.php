<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Enums;

enum ValidationError: string
{
    case REQUIRED = 'required';
    case INVALID_FORMAT = 'invalid_format';
    case TOO_LOW = 'too_low';
    case TOO_HIGH = 'too_high';
    case EMPTY = 'empty';
}
