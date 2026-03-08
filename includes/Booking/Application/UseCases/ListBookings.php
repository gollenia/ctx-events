<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Application\Services\BookingEnrichmentService;

use Contexis\Events\Booking\Domain\BookingRepository;

final class ListBookings
{
    public function __construct(
        private BookingRepository $repository,
        private BookingEnrichmentService $enrichmentService,
    ) {
    }

    public function execute(BookingListRequest $query): BookingListResponse
    {
        $response = $this->repository->search($query)->withEnrichment(fn(BookingListItem $item) => $this->enrichmentService->enrichListItem($item));

        return $this->enrichmentService->enrichList($response);
    }
}
