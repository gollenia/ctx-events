<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

enum Order: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}
