<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\ValueObjects;

enum Order: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}
