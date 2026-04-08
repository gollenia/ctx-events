<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class ListBookings
{
    public function __construct(
        private BookingRepository $repository,
		private GatewayRepository $gatewayRepository,
		private TransactionRepository $transactionRepository,
    ) {
    }

    public function execute(BookingListRequest $query): BookingListResponse
    {
        $response = $this->repository->search($query);
        $transactionsByBookingId = $this->transactionRepository->findByBookingIds(array_map(
            static fn (BookingListItem $item): BookingId => BookingId::from($item->id),
            $response->toArray()
        ));
        $gatewayNames = [];

		return $response->withEnrichment(function (BookingListItem $item) use (&$gatewayNames, $transactionsByBookingId) {
			if ($item->gateway !== null) {
                if (!array_key_exists($item->gateway, $gatewayNames)) {
                    $gatewayNames[$item->gateway] = $this->gatewayRepository
                        ->find($item->gateway)
                        ?->getAdminName();
                }

				if ($gatewayNames[$item->gateway] !== null) {
					$item = $item->withGatewayName($gatewayNames[$item->gateway]);
				}
			}

            $transaction = $transactionsByBookingId[$item->id]?->first();
            $transactionId = $transaction?->externalId;
            if ($transactionId === '') {
                $transactionId = null;
            }

            $item = $item->withTransactionDetails(
                $transactionId,
                $transaction?->expiresAt,
            );

			return $item;
		});
	}
}
