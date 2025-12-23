<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

enum NumberVariant: string
{
    case INPUT = 'input';
    case SLIDER = 'slider';
}