<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Application\Contracts\ReferenceGeneratorContract;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;

final class FakeReferenceGenerator implements ReferenceGeneratorContract
{
    private int $sequence = 1;

    public function create(): BookingReference
    {
        return BookingReference::fromString(str_pad((string) $this->sequence++, 12, 'A', STR_PAD_RIGHT));
    }
}
