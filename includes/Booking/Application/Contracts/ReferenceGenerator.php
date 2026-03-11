<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;

interface ReferenceGenerator
{
    public function create(string $prefix = '', string $suffix = ''): BookingReference;
}
