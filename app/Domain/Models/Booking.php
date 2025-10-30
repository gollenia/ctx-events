<?php

namespace Contexis\Events\Domain\Models;
use Contexis\Events\Domain\ValueObjects\Email;
use Contexis\Events\Domain\ValueObjects\PriceSummary;

final class Booking {
	public function __construct(
		public readonly string $id,
		public readonly Event $event,
		public readonly Email $user_email,
		public readonly PriceSummary $price_summary,
		public readonly \DateTimeImmutable $created_at,
		public readonly BookingStatus $status,
		public readonly ?array $registration,
		public readonly ?array $attendees,
		public readonly ?string $gateway,
		public readonly ?Coupon $coupon,
		public readonly ?TransactionCollection $transactions,
		public readonly ?RecordCollection $notes,
		public readonly ?RecordCollection $log
	) {}
}


