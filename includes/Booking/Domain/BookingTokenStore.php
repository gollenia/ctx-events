<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;

interface BookingTokenStore
{
    public function save(BookingTokenRecord $token): void;

    public function find(string $tokenId): ?BookingTokenRecord;

    public function delete(string $tokenId): void;
}
