<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Payment\Application\Services\GatewayService;

final class BookingEnrichmentService
{
    public function __construct(private GatewayService $gatewayService)
    {
    }

    public function enrichList(BookingListResponse $response): BookingListResponse
    {
            return $response->withEnrichment(\Closure::fromCallable([$this, 'enrichListItem']));
    }

    public function enrichListItem(BookingListItem $item): BookingListItem
    {
        if ($item->gateway === null) {
            return $item;
        }

        return $item->withGatewayName($this->gatewayService->findNameBySlug($item->gateway));
    }
}
