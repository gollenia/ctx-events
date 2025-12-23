<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

enum CheckboxVariant: string
{
    case DEFAULT = 'default';
    case SWITCH = 'switch';
}
