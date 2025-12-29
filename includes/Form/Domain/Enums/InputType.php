<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Enums;

enum InputType: string
{
    case EMAIL = 'email';
    case TEL = 'tel';
    case URL = 'url';
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATE = 'date';
}
