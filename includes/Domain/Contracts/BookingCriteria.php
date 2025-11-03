<?php


namespace Contexis\Events\Domain\Contracts;

final class BookingCriteria
{
    public ?int $event_id = null;
    public ?int $coupon_id = null;
    public ?string $user_email = null;
    public ?string $gateway = null;
    public ?string $search = null;
    /** @var BookingStatus[] */
    public array $statuses = [];
    public ?string $order_by = 'date';
    public string $order = 'DESC';
    public ?int $limit = null;
    public ?int $offset = null;
}