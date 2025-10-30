<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Transaction;
use Contexis\Events\PostTypes\BookingPost;
use DateTime;

class TransactionMapper implements \Contexis\Events\Core\Contracts\Mapper
{

	private static array $errors = [];

	public static function map(array $data): ?Transaction
	{
		$amount  = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
		$gateway = $data['gateway'] ?? '';

		$transaction = new Transaction;
		$transaction->created_at = new DateTime($data['date'] ?? 'now');
		$transaction->expires_at = isset($data['expires_at']) ? new DateTime($data['expires_at']) : null;
		$transaction->amount = $amount;
		$transaction->gateway = $gateway;
		$transaction->external_id = (string)($data['id'] ?? '');
		$transaction->status = (string)($data['status'] ?? 'pending');
		$transaction->checkout_url = (string)($data['checkout_url'] ?? '');
		$transaction->booking_id = (int)($data['booking_id'] ?? 0);

		return $transaction;
	}

	
}