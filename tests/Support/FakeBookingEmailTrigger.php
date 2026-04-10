<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

final class FakeBookingEmailTrigger implements BookingEmailTrigger
{
    /** @var list<array{trigger: EmailTrigger, bookingId: BookingId, cancellationReason: ?string}> */
    public array $calls = [];

    public BookingEmailResult $result;

    public function __construct(?BookingEmailResult $result = null)
    {
        $this->result = $result ?? BookingEmailResult::empty();
    }

    public function trigger(
        EmailTrigger $trigger,
        BookingId $bookingId,
        ?string $cancellationReason = null,
    ): BookingEmailResult
    {
        $this->calls[] = [
            'trigger' => $trigger,
            'bookingId' => $bookingId,
            'cancellationReason' => $cancellationReason,
        ];

        return $this->result;
    }

    /** @return array{trigger: EmailTrigger, bookingId: BookingId, cancellationReason: ?string}|null */
    public function lastCall(): ?array
    {
        if ($this->calls === []) {
            return null;
        }

        return $this->calls[array_key_last($this->calls)];
    }
}
