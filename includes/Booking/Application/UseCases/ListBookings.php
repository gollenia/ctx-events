<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Application\Services\BookingEnrichmentService;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Payment\Domain\GatewayRepository;

final class ListBookings
{
    public function __construct(
        private BookingRepository $repository,
		private GatewayRepository $gatewayRepository
    ) {
    }

    public function execute(BookingListRequest $query): BookingListResponse
    {
        $response = $this->repository->search($query);
		$response->map(function (BookingListItem $item) {
			if ($item->gateway !== null) {
				$item = $item->withGatewayName($this->gatewayRepository->find($item->gateway)->getAdminName());
			}

			return $item;
		});

		return $response;
	}
}
