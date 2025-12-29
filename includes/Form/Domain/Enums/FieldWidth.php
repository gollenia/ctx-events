<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

enum FieldWidth: int
{
    case ONE = 1;
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;
    case SIX = 6;
}
