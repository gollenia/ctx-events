<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Application\Contracts\ReferenceGenerator;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;

final class FakeReferenceGenerator implements ReferenceGenerator
{
    private int $sequence = 1;

    public function create(string $prefix = '', string $suffix = ''): BookingReference
    {
        $reference = str_pad((string) $this->sequence++, 12, 'A', STR_PAD_RIGHT);

        return BookingReference::fromString($prefix . $reference . $suffix);
    }
}
