<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript (name: 'BookingListItem')]
final readonly class BookingListItemResource implements Resource
{
    public function __construct(
        public string $reference,
        public Email $email,
        public PersonName $name,
		/** @var array{id: int, title: string} */
        public array $event,
        public int $status,
        public PriceSummaryResource $priceSummary,
        public int $spaces,
		/** @var array{slug: string, name: string}|null */
        public ?array $gateway,
        public string $date,
        public ?string $transactionId,
        public ?string $transactionExpiresAt,
    ) {
    }

    public static function fromDTO(BookingListItem $item): self
    {
        $gateway = $item->gateway !== null
            ? ['slug' => $item->gateway, 'name' => $item->gatewayName ?? $item->gateway]
            : null;

        return new self(
            reference: $item->reference,
            email: $item->email,
            name: $item->name,
            event: ['id' => $item->eventId->toInt(), 'title' => $item->eventTitle],
            status: $item->status,
            priceSummary: PriceSummaryResource::from($item->priceSummary),
            spaces: $item->spaces,
            gateway: $gateway,
            date: $item->bookingTime->format(DATE_ATOM),
            transactionId: $item->transactionId,
            transactionExpiresAt: $item->transactionExpiresAt?->format(DATE_ATOM),
        );
    }

    public function jsonSerialize(): array
    {
        return [

            'reference' => $this->reference,
            'email'     => $this->email->toString(),
            'name'      => $this->name->toArray(),
            'event'     => $this->event,
            'status'    => $this->status,
            'priceSummary' => $this->priceSummary,
            'spaces'    => $this->spaces,
            'gateway'   => $this->gateway,
            'date'      => $this->date,
            'transactionId' => $this->transactionId,
            'transactionExpiresAt' => $this->transactionExpiresAt,
        ];
    }
}
