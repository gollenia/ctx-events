<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Enums;

enum CheckboxVariant: string
{
    case DEFAULT = 'default';
    case SWITCH = 'switch';
}
